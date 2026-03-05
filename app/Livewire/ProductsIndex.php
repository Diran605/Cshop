<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\BulkType;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesItem;
use App\Models\StockInItem;
use App\Models\StockInReceipt;
use App\Models\StockMovement;
use App\Models\UnitType;
use App\Support\ActivityLogger;
use App\Exports\ProductsTemplateExport;
use App\Imports\ProductsImport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ProductsIndex extends Component
{
    use WithPagination;

    public string $mode = 'manage';

    public int $branch_id = 0;
    public string $search = '';
    public string $name = '';
    public ?string $description = null;
    public ?int $category_id = null;
    public ?string $cost_price = null;
    public ?string $min_selling_price = null;
    public string $selling_price = '0.00';
    public bool $bulk_enabled = false;
    public ?int $bulk_type_id = null;
    public ?int $unit_type_id = null;
    public string $status = 'active';
    public ?int $editingId = null;

    public int $opening_quantity = 0;
    public ?string $opening_cost_price = null;
    public ?string $opening_expiry_date = null;

    public string $product_date = '';

    public bool $show_edit_modal = false;

    public bool $show_delete_modal = false;
    public int $pending_delete_id = 0;
    public string $pending_delete_name = '';

    public bool $isSuperAdmin = false;

    public int $auth_user_id = 0;

    public $excel_file = null;

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer', 'min:1', Rule::exists('categories', 'id')->where('branch_id', $this->branch_id)],
            'unit_type_id' => ['nullable', 'integer', 'min:1', Rule::exists('unit_types', 'id')->where('branch_id', $this->branch_id)],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'min_selling_price' => ['nullable', 'numeric', 'min:0', 'lte:selling_price'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'bulk_enabled' => ['boolean'],
            'bulk_type_id' => ['nullable', 'integer', 'min:1', Rule::exists('bulk_types', 'id')->where('branch_id', $this->branch_id)],
            'status' => ['required', 'string', 'in:active,inactive'],
            'opening_quantity' => ['nullable', 'integer', 'min:0'],
            'opening_cost_price' => ['nullable', 'numeric', 'min:0'],
            'opening_expiry_date' => ['nullable', 'date'],
            'product_date' => ['required', 'date'],
        ];
    }

    public function mount(string $mode = 'manage'): void
    {
        $mode = strtolower(trim($mode));
        $this->mode = in_array($mode, ['add', 'manage', 'expired'], true) ? $mode : 'manage';

        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->auth_user_id = (int) ($user?->id ?? 0);

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user->branch_id ?? 0);
        } else {
            $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        }

        $this->product_date = Carbon::today()->toDateString();
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;
            $this->resetForm();
        }

        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        }
    }

    public function save(): void
    {
        $this->syncAuthContext();

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) (auth()->user()?->branch_id ?? 0);
        }

        $data = $this->validate();

        $data['branch_id'] = (int) $this->branch_id;

        $productData = $data;
        unset($productData['opening_quantity'], $productData['opening_cost_price'], $productData['opening_expiry_date'], $productData['product_date']);

        // Set created_at from product_date
        $productData['created_at'] = Carbon::parse($this->product_date)->startOfDay();
        $productData['updated_at'] = now();

        if (! $data['bulk_enabled']) {
            $data['bulk_type_id'] = null;
            $productData['bulk_type_id'] = null;
        }

        if ($this->editingId) {
            $before = Product::query()->find($this->editingId);
            
            \Log::info('Before update', [
                'editingId' => $this->editingId,
                'before' => $before->toArray(),
                'form_data' => $data,
                'productData' => $productData
            ]);
            
            DB::transaction(function () use ($productData, $data, $before) {
                Product::query()->whereKey($this->editingId)->update($productData);

                // Handle opening stock updates if provided
                if (isset($data['opening_quantity']) && $data['opening_quantity'] > 0) {
                    $openingQty = max(0, (int) $data['opening_quantity']);
                    $openingCost = ($data['opening_cost_price'] !== null && $data['opening_cost_price'] !== '')
                        ? number_format((float) $data['opening_cost_price'], 2, '.', '')
                        : null;
                    $openingExpiry = ($data['opening_expiry_date'] ?? null) ?: null;

                    $this->ensureProductStockForBranch((int) $this->editingId, (int) $this->branch_id);

                    // Get current stock
                    $currentStock = ProductStock::query()
                        ->where('product_id', (int) $this->editingId)
                        ->where('branch_id', (int) $this->branch_id)
                        ->first();

                    $currentQty = $currentStock ? (int) $currentStock->current_stock : 0;
                    $adjustment = $openingQty - $currentQty;

                    if ($adjustment !== 0) {
                        // Create stock adjustment receipt
                        $receipt = StockInReceipt::query()->create([
                            'receipt_no' => 'SA-' . strtoupper(Str::random(10)),
                            'branch_id' => (int) $this->branch_id,
                            'user_id' => auth()->id(),
                            'received_at' => now(),
                            'notes' => 'Stock adjustment for product: ' . $before->name . ' (from ' . $currentQty . ' to ' . $openingQty . ')',
                            'total_quantity' => abs($adjustment),
                            'total_cost' => $openingCost !== null ? number_format(((float) $openingCost) * abs($adjustment), 2, '.', '') : null,
                        ]);

                        StockInItem::query()->create([
                            'stock_in_receipt_id' => (int) $receipt->id,
                            'product_id' => (int) $this->editingId,
                            'entry_mode' => 'unit',
                            'bulk_quantity' => null,
                            'units_per_bulk' => null,
                            'bulk_type_id' => null,
                            'expiry_date' => $openingExpiry,
                            'quantity' => abs($adjustment),
                            'remaining_quantity' => abs($adjustment),
                            'cost_price' => $openingCost,
                            'notes' => $adjustment > 0 ? 'Stock increase' : 'Stock decrease',
                        ]);

                        // Update the product stock
                        if ($currentStock) {
                            $currentStock->current_stock = $openingQty;
                            if ($openingCost !== null) {
                                $currentStock->cost_price = $openingCost;
                            }
                            $currentStock->save();
                        }

                        ActivityLogger::log(
                            'stock_in.created',
                            $receipt,
                            'Stock adjustment created',
                            [
                                'receipt_no' => $receipt->receipt_no,
                                'product_id' => (int) $this->editingId,
                                'previous_quantity' => $currentQty,
                                'new_quantity' => $openingQty,
                                'adjustment' => $adjustment,
                                'cost_price' => $openingCost,
                                'expiry_date' => $openingExpiry,
                            ],
                            (int) $this->branch_id
                        );
                    }
                }
            });

            $after = Product::query()->find($this->editingId);
            
            \Log::info('After update', [
                'after' => $after->toArray(),
                'changes' => array_diff_assoc($after->toArray(), $before->toArray())
            ]);
            
            ActivityLogger::log(
                'product.updated',
                $after,
                'Product updated',
                [
                    'before' => $before ? $before->only(['name', 'category_id', 'unit_type_id', 'cost_price', 'min_selling_price', 'selling_price', 'bulk_enabled', 'bulk_type_id', 'status', 'branch_id']) : null,
                    'after' => $after ? $after->only(['name', 'category_id', 'unit_type_id', 'cost_price', 'min_selling_price', 'selling_price', 'bulk_enabled', 'bulk_type_id', 'status', 'branch_id']) : null,
                ],
                $after?->branch_id ? (int) $after->branch_id : null
            );

            $this->show_edit_modal = false;
            session()->flash('status', 'Product updated successfully.');
        } else {
            $openingQty = max(0, (int) ($data['opening_quantity'] ?? 0));
            $openingCost = ($data['opening_cost_price'] !== null && $data['opening_cost_price'] !== '')
                ? number_format((float) $data['opening_cost_price'], 2, '.', '')
                : null;
            $openingExpiry = ($data['opening_expiry_date'] ?? null) ?: null;

            DB::transaction(function () use ($productData, $openingQty, $openingCost, $openingExpiry) {
                $product = Product::query()->create($productData);

                ActivityLogger::log(
                    'product.created',
                    $product,
                    'Product created',
                    [
                        'name' => $product->name,
                        'branch_id' => $product->branch_id,
                        'unit_type_id' => $product->unit_type_id,
                        'selling_price' => $product->selling_price,
                        'cost_price' => $product->cost_price,
                        'min_selling_price' => $product->min_selling_price,
                        'bulk_enabled' => $product->bulk_enabled,
                        'bulk_type_id' => $product->bulk_type_id,
                        'status' => $product->status,
                        'opening_quantity' => $openingQty,
                        'opening_cost_price' => $openingCost,
                        'opening_expiry_date' => $openingExpiry,
                    ],
                    $product->branch_id ? (int) $product->branch_id : null
                );

                $this->ensureProductStockForBranch((int) $product->id, (int) $product->branch_id);

                if ($openingQty > 0) {
                    $stock = ProductStock::query()
                        ->where('branch_id', (int) $product->branch_id)
                        ->where('product_id', (int) $product->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $beforeStock = (int) $stock->current_stock;
                    $afterStock = $beforeStock + $openingQty;
                    $stock->current_stock = $afterStock;

                    if ($openingCost !== null) {
                        $stock->cost_price = $openingCost;
                    }

                    $stock->save();

                    $receipt = StockInReceipt::query()->create([
                        'receipt_no' => 'OS-' . strtoupper(Str::random(10)),
                        'branch_id' => (int) $product->branch_id,
                        'user_id' => auth()->id(),
                        'received_at' => Carbon::parse($this->product_date)->startOfDay(),
                        'notes' => 'OPENING STOCK',
                        'total_quantity' => $openingQty,
                        'total_cost' => $openingCost !== null ? number_format(((float) $openingCost) * $openingQty, 2, '.', '') : null,
                    ]);

                    StockInItem::query()->create([
                        'stock_in_receipt_id' => (int) $receipt->id,
                        'product_id' => (int) $product->id,
                        'entry_mode' => 'unit',
                        'bulk_quantity' => null,
                        'units_per_bulk' => null,
                        'bulk_type_id' => null,
                        'expiry_date' => $openingExpiry,
                        'quantity' => $openingQty,
                        'remaining_quantity' => $openingQty,
                        'cost_price' => $openingCost,
                        'line_total' => $openingCost !== null ? number_format(((float) $openingCost) * $openingQty, 2, '.', '') : null,
                    ]);

                    StockMovement::query()->create([
                        'branch_id' => (int) $product->branch_id,
                        'product_id' => (int) $product->id,
                        'user_id' => auth()->id(),
                        'movement_type' => 'IN',
                        'quantity' => $openingQty,
                        'before_stock' => $beforeStock,
                        'after_stock' => $afterStock,
                        'unit_cost' => $openingCost,
                        'unit_price' => null,
                        'stock_in_receipt_id' => (int) $receipt->id,
                        'sales_receipt_id' => null,
                        'moved_at' => now(),
                        'notes' => 'OPENING STOCK',
                    ]);
                }
            });
        }

        if ($this->show_edit_modal) {
            $this->closeEditModal();
            return;
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $this->syncAuthContext();

        $product = Product::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        if ($this->isSuperAdmin) {
            $this->branch_id = (int) ($product->branch_id ?? $this->branch_id);
        }
        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->category_id = $product->category_id ? (int) $product->category_id : null;
        $this->unit_type_id = $product->unit_type_id ? (int) $product->unit_type_id : null;
        $this->cost_price = $product->cost_price !== null ? (string) $product->cost_price : null;
        $this->min_selling_price = $product->min_selling_price !== null ? (string) $product->min_selling_price : null;
        $this->selling_price = (string) $product->selling_price;
        $this->bulk_enabled = (bool) $product->bulk_enabled;
        $this->bulk_type_id = $product->bulk_type_id ? (int) $product->bulk_type_id : null;
        $this->status = $product->status ?? 'active';
        $this->product_date = $product->created_at ? $product->created_at->toDateString() : Carbon::today()->toDateString();
        
        // Load current stock values for editing
        $currentStock = ProductStock::query()
            ->where('product_id', $product->id)
            ->where('branch_id', $this->branch_id)
            ->first();
            
        $this->opening_quantity = $currentStock ? (int) $currentStock->current_stock : 0;
        $this->opening_cost_price = $currentStock && $currentStock->cost_price !== null ? (string) $currentStock->cost_price : null;
        $this->opening_expiry_date = null; // We don't track expiry in ProductStock, so leave empty for manual entry
    }

    public function openEditModal(int $id): void
    {
        $this->edit($id);
        $this->show_edit_modal = true;
    }

    public function closeEditModal(): void
    {
        $this->show_edit_modal = false;
        $this->resetForm();
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function updatedBulkEnabled(bool $value): void
    {
        if (! $value) {
            $this->bulk_type_id = null;
            return;
        }

        if ($this->bulk_type_id) {
            return;
        }

        $this->bulk_type_id = BulkType::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->value('id');

        if ($this->bulk_type_id) {
            $this->bulk_type_id = (int) $this->bulk_type_id;
        }
    }

    public function updatedBranchId(): void
    {
        if (! $this->isSuperAdmin || $this->editingId) {
            return;
        }

        $this->category_id = null;
        $this->bulk_enabled = false;
        $this->bulk_type_id = null;
    }

    public function delete(int $id): void
    {
        $this->syncAuthContext();

        $product = Product::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        $hasSales = SalesItem::query()->where('product_id', (int) $product->id)->exists();
        $hasStockIn = StockInItem::query()->where('product_id', (int) $product->id)->exists();
        $hasMovement = StockMovement::query()->where('product_id', (int) $product->id)->exists();

        if ($hasSales || $hasStockIn || $hasMovement) {
            session()->flash('status', 'Cannot delete product because it has transaction history. Set it to inactive instead.');
            return;
        }

        $snapshot = $product->only(['id', 'name', 'branch_id', 'status']);

        ProductStock::query()->where('product_id', (int) $product->id)->delete();
        $product->delete();

        ActivityLogger::log(
            'product.deleted',
            ['type' => Product::class, 'id' => (int) $id],
            'Product deleted',
            ['product' => $snapshot],
            isset($snapshot['branch_id']) ? (int) $snapshot['branch_id'] : null
        );
        $this->resetForm();
    }

    public function openDeleteModal(int $id): void
    {
        $this->syncAuthContext();

        $product = Product::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        $this->pending_delete_id = (int) $product->id;
        $this->pending_delete_name = (string) $product->name;
        $this->show_delete_modal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->show_delete_modal = false;
        $this->pending_delete_id = 0;
        $this->pending_delete_name = '';
    }

    public function confirmDelete(): void
    {
        $id = (int) $this->pending_delete_id;
        $this->closeDeleteModal();

        if ($id > 0) {
            $this->delete($id);
        }
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new ProductsTemplateExport, 'products_template.xlsx');
    }

    public function importExcel(): void
    {
        $this->validate([
            'excel_file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new ProductsImport, $this->excel_file);

        session()->flash('status', 'Products imported successfully.');
        $this->excel_file = null;
    }

    private function resetForm(): void
    {
        $currentBranchId = (int) $this->branch_id;

        $this->reset([
            'search',
            'name',
            'description',
            'category_id',
            'unit_type_id',
            'cost_price',
            'min_selling_price',
            'selling_price',
            'bulk_enabled',
            'bulk_type_id',
            'status',
            'editingId',
            'opening_quantity',
            'opening_cost_price',
            'opening_expiry_date',
        ]);

        $this->selling_price = '0.00';
        $this->cost_price = null;
        $this->min_selling_price = null;
        $this->bulk_enabled = false;
        $this->bulk_type_id = null;
        $this->status = 'active';
        $this->opening_quantity = 0;
        $this->opening_cost_price = null;
        $this->opening_expiry_date = null;

        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        } else {
            $this->branch_id = $currentBranchId > 0
                ? $currentBranchId
                : (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        }
    }

    private function ensureProductStockForBranch(int $productId, int $branchId): void
    {
        if ($branchId <= 0) {
            return;
        }

        ProductStock::query()->firstOrCreate(
            ['branch_id' => $branchId, 'product_id' => $productId],
            ['current_stock' => 0, 'minimum_stock' => 0, 'cost_price' => null]
        );
    }

    public function render()
    {
        $this->syncAuthContext();

        $today = Carbon::today()->toDateString();

        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();

        $categories = Category::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->get();

        $bulkTypes = BulkType::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->get();

        $unitTypes = UnitType::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $products = collect();
        if ($this->mode !== 'add') {
            $expiredQtyMap = [];
            $expiredProductIds = [];

            if ($this->mode === 'expired') {
                $expiredRows = StockInItem::query()
                    ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
                    ->whereNull('stock_in_receipts.voided_at')
                    ->where('stock_in_items.remaining_quantity', '>', 0)
                    ->whereNotNull('stock_in_items.expiry_date')
                    ->where('stock_in_items.expiry_date', '<', $today)
                    ->when($this->branch_id > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $this->branch_id))
                    ->selectRaw('stock_in_items.product_id as product_id, SUM(stock_in_items.remaining_quantity) as qty')
                    ->groupBy('stock_in_items.product_id')
                    ->get();

                $expiredQtyMap = $expiredRows->pluck('qty', 'product_id')->map(fn ($v) => (int) $v)->all();
                $expiredProductIds = array_keys($expiredQtyMap);

                if (count($expiredProductIds) === 0) {
                    $products = collect();

                    return view('livewire.products-index', [
                        'products' => $products,
                        'categories' => $categories,
                        'bulkTypes' => $bulkTypes,
                        'branches' => $branches,
                        'isSuperAdmin' => $this->isSuperAdmin,
                        'expiredQtyMap' => $expiredQtyMap,
                    ]);
                }
            }

            $products = Product::query()
                ->with(['category', 'bulkType', 'branch', 'stocks'])
                ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
                ->when($this->mode === 'expired', fn ($q) => $q->whereIn('id', $expiredProductIds))
                ->when(trim($this->search) !== '', function ($q) {
                    $term = '%' . trim($this->search) . '%';
                    $q->where(function ($qq) use ($term) {
                        $qq->where('name', 'like', $term)
                            ->orWhere('description', 'like', $term);
                    });
                })
                ->orderBy('name')
                ->paginate(20);

        $products->getCollection()->transform(function ($product) {
            $product->current_stock = $product->stocks->sum('current_stock');
            return $product;
        });
        }

        return view('livewire.products-index', [
            'products' => $products,
            'categories' => $categories,
            'bulkTypes' => $bulkTypes,
            'unitTypes' => $unitTypes,
            'branches' => $branches,
            'isSuperAdmin' => $this->isSuperAdmin,
            'expiredQtyMap' => $expiredQtyMap ?? [],
        ]);
    }
}
