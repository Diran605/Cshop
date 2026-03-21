<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockInItem;
use App\Models\StockInReceipt;
use App\Models\StockMovement;
use App\Support\ActivityLogger;
use App\Exports\StockInTemplateExport;
use App\Imports\StockInImport;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

class StockInIndex extends Component
{
    public int $branch_id = 0;
    public int $product_id = 0;

    public string $product_search = '';

    public string $entry_mode = 'unit';
    public int $bulk_quantity = 1;

    public int $quantity = 1;
    public ?string $cost_price = null;

    public ?string $supplier_name = null;

    public ?string $batch_ref_no = null;

    public ?string $expiry_date = null;

    public string $received_at_date = '';

    public bool $isSuperAdmin = false;

    public int $auth_user_id = 0;

    public ?string $notes = null;
    public int $selected_receipt_id = 0;

    public array $draft_lines = [];
    public int $draft_seq = 0;

    public $excel_file = null;

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'min:1'],
            'product_id' => ['required', 'integer', 'min:1'],
            'entry_mode' => ['required', 'string', 'in:unit,bulk'],
            'bulk_quantity' => ['nullable', 'integer', 'min:1', 'required_if:entry_mode,bulk'],
            'quantity' => ['nullable', 'integer', 'min:1', 'required_if:entry_mode,unit'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'batch_ref_no' => ['nullable', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'received_at_date' => ['required', 'date'],
        ];
    }

    public function mount(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->auth_user_id = (int) ($user?->id ?? 0);

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user->branch_id ?? 0);
        } else {
            $this->branch_id = 0;
        }

        $this->product_id = 0;
        $this->entry_mode = 'unit';
        $this->bulk_quantity = 1;
        $this->quantity = 1;
        $this->cost_price = null;
        $this->supplier_name = null;
        $this->batch_ref_no = null;
        $this->expiry_date = null;
        $this->notes = null;
        $this->selected_receipt_id = 0;

        $this->received_at_date = Carbon::today()->toDateString();

        $this->draft_lines = [];
        $this->draft_seq = 0;

        $this->product_search = '';
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;
            $this->selected_receipt_id = 0;
            $this->notes = null;
            $this->entry_mode = 'unit';
            $this->bulk_quantity = 1;
            $this->quantity = 1;
            $this->cost_price = null;
            $this->supplier_name = null;
            $this->batch_ref_no = null;
            $this->expiry_date = null;
            $this->received_at_date = Carbon::today()->toDateString();
            $this->product_search = '';

            $this->draft_lines = [];
            $this->draft_seq = 0;
        }

        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
    }

    public function updatedProductId(): void
    {
        $product = Product::query()
            ->with(['bulkType'])
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->find($this->product_id);
        if ($product && (bool) $product->bulk_enabled) {
            $this->entry_mode = 'bulk';
            $this->bulk_quantity = max(1, (int) $this->bulk_quantity);
            return;
        }

        $this->entry_mode = 'unit';
        $this->bulk_quantity = 1;
        $this->quantity = max(1, (int) $this->quantity);
    }

    public function updatedBranchId(): void
    {
        if (! $this->isSuperAdmin) {
            return;
        }

        $this->product_id = 0;
        $this->product_search = '';
        $this->entry_mode = 'unit';
        $this->bulk_quantity = 1;
        $this->quantity = 1;
        $this->supplier_name = null;
        $this->batch_ref_no = null;
        $this->expiry_date = null;
    }

    public function addDraftLine(): void
    {
        $this->resetErrorBag('draft_lines');

        if ($this->expiry_date !== null && trim((string) $this->expiry_date) === '') {
            $this->expiry_date = null;
        }

        $data = $this->validate([
            'branch_id' => ['required', 'integer', 'min:1'],
            'product_id' => ['required', 'integer', 'min:1'],
            'entry_mode' => ['required', 'string', 'in:unit,bulk'],
            'bulk_quantity' => ['nullable', 'integer', 'min:1', 'required_if:entry_mode,bulk'],
            'quantity' => ['nullable', 'integer', 'min:1', 'required_if:entry_mode,unit'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'batch_ref_no' => ['nullable', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
        ]);

        if (array_key_exists('supplier_name', $data)) {
            $supplier = trim((string) ($data['supplier_name'] ?? ''));
            $data['supplier_name'] = $supplier !== '' ? mb_substr($supplier, 0, 255) : null;
        }

        if (array_key_exists('batch_ref_no', $data)) {
            $ref = trim((string) ($data['batch_ref_no'] ?? ''));
            $data['batch_ref_no'] = $ref !== '' ? mb_substr($ref, 0, 100) : null;
        }

        if (! $this->isSuperAdmin) {
            $data['branch_id'] = (int) (auth()->user()?->branch_id ?? 0);
            $this->branch_id = (int) $data['branch_id'];
        }

        $product = Product::query()
            ->with(['bulkType'])
            ->when((int) $data['branch_id'] > 0, fn ($q) => $q->where('branch_id', (int) $data['branch_id']))
            ->find((int) $data['product_id']);
        if (! $product) {
            return;
        }

        $mode = (string) $data['entry_mode'];
        if ($mode === 'bulk' && (! (bool) $product->bulk_enabled || ! $product->bulkType)) {
            $mode = 'unit';
        }

        $bulkTypeId = null;
        $unitsPerBulk = null;
        $bulkQty = null;
        $unitsQty = 0;

        if ($mode === 'bulk') {
            $bulkTypeId = (int) $product->bulkType->id;
            $unitsPerBulk = (int) $product->bulkType->units_per_bulk;
            $bulkQty = max(1, (int) ($data['bulk_quantity'] ?? 1));

            if ($unitsPerBulk <= 0) {
                $this->addError('draft_lines', 'Invalid units per bulk configuration.');
                return;
            }

            $unitsQty = $bulkQty * $unitsPerBulk;
        } else {
            $unitsQty = max(1, (int) ($data['quantity'] ?? 1));
        }

        $incomingCostRaw = ($data['cost_price'] !== null && $data['cost_price'] !== '') ? (float) $data['cost_price'] : null;
        $unitCost = null;
        if ($incomingCostRaw !== null) {
            if ($mode === 'bulk') {
                $unitCost = ($unitsPerBulk !== null && $unitsPerBulk > 0) ? ($incomingCostRaw / $unitsPerBulk) : null;
            } else {
                $unitCost = $incomingCostRaw;
            }
        }

        $key = ++$this->draft_seq;
        $this->draft_lines[$key] = [
            'key' => (int) $key,
            'product_id' => (int) $product->id,
            'name' => (string) $product->name,
            'unit_type_name' => $product->unitType?->name,
            'supplier_name' => ($data['supplier_name'] ?? null) ?: null,
            'batch_ref_no' => ($data['batch_ref_no'] ?? null) ?: null,
            'entry_mode' => $mode,
            'bulk_quantity' => $bulkQty,
            'units_per_bulk' => $unitsPerBulk,
            'bulk_type_id' => $bulkTypeId,
            'expiry_date' => ($data['expiry_date'] ?? null) ?: null,
            'quantity' => (int) $unitsQty,
            'cost_price' => $unitCost !== null ? number_format((float) $unitCost, 2, '.', '') : null,
        ];

        $this->product_id = 0;
        $this->entry_mode = 'unit';
        $this->bulk_quantity = 1;
        $this->quantity = 1;
        $this->cost_price = null;
        $this->supplier_name = null;
        $this->batch_ref_no = null;
        $this->expiry_date = null;
    }

    public function removeDraftLine(int $key): void
    {
        unset($this->draft_lines[$key]);
    }

    public function clearDraftLines(): void
    {
        $this->draft_lines = [];
        $this->draft_seq = 0;
        $this->resetErrorBag('draft_lines');
    }

    public function incrementDraftLine(int $key): void
    {
        $this->resetErrorBag('draft_lines');

        if (! isset($this->draft_lines[$key])) {
            return;
        }

        $mode = (string) ($this->draft_lines[$key]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $this->draft_lines[$key]['bulk_quantity'] = (int) ($this->draft_lines[$key]['bulk_quantity'] ?? 0) + 1;
            $this->draft_lines[$key]['quantity'] = (int) $this->draft_lines[$key]['bulk_quantity'] * (int) ($this->draft_lines[$key]['units_per_bulk'] ?? 0);
            return;
        }

        $this->draft_lines[$key]['quantity'] = (int) ($this->draft_lines[$key]['quantity'] ?? 0) + 1;
    }

    public function decrementDraftLine(int $key): void
    {
        $this->resetErrorBag('draft_lines');

        if (! isset($this->draft_lines[$key])) {
            return;
        }

        $mode = (string) ($this->draft_lines[$key]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $newBulkQty = (int) ($this->draft_lines[$key]['bulk_quantity'] ?? 0) - 1;
            if ($newBulkQty <= 0) {
                unset($this->draft_lines[$key]);
                return;
            }

            $this->draft_lines[$key]['bulk_quantity'] = $newBulkQty;
            $this->draft_lines[$key]['quantity'] = $newBulkQty * (int) ($this->draft_lines[$key]['units_per_bulk'] ?? 0);
            return;
        }

        $newQty = (int) ($this->draft_lines[$key]['quantity'] ?? 0) - 1;
        if ($newQty <= 0) {
            unset($this->draft_lines[$key]);
            return;
        }

        $this->draft_lines[$key]['quantity'] = $newQty;
    }

    public function setDraftLineQuantity(int $key, mixed $quantity): void
    {
        $this->resetErrorBag('draft_lines');

        if (! isset($this->draft_lines[$key])) {
            return;
        }

        $qty = (int) $quantity;
        if ($qty <= 0) {
            unset($this->draft_lines[$key]);
            return;
        }

        $mode = (string) ($this->draft_lines[$key]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $this->draft_lines[$key]['bulk_quantity'] = $qty;
            $this->draft_lines[$key]['quantity'] = $qty * (int) ($this->draft_lines[$key]['units_per_bulk'] ?? 0);
            return;
        }

        $this->draft_lines[$key]['quantity'] = $qty;
    }

    public function setDraftLineCostPrice(int $key, mixed $costPrice): void
    {
        $this->resetErrorBag('draft_lines');

        if (! isset($this->draft_lines[$key])) {
            return;
        }

        if ($costPrice === null || trim((string) $costPrice) === '') {
            $this->draft_lines[$key]['cost_price'] = null;
            return;
        }

        $v = (float) $costPrice;
        if ($v < 0) {
            $v = 0;
        }

        $mode = (string) ($this->draft_lines[$key]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $unitsPerBulk = (int) ($this->draft_lines[$key]['units_per_bulk'] ?? 0);
            if ($unitsPerBulk > 0) {
                $this->draft_lines[$key]['cost_price'] = number_format($v / $unitsPerBulk, 2, '.', '');
            }
            return;
        }

        $this->draft_lines[$key]['cost_price'] = number_format($v, 2, '.', '');
    }

    public function setDraftLineSupplierName(int $key, mixed $supplierName): void
    {
        $this->resetErrorBag('draft_lines');

        if (! isset($this->draft_lines[$key])) {
            return;
        }

        $value = trim((string) $supplierName);
        $this->draft_lines[$key]['supplier_name'] = $value !== '' ? mb_substr($value, 0, 255) : null;
    }

    public function setDraftLineBatchRefNo(int $key, mixed $batchRefNo): void
    {
        $this->resetErrorBag('draft_lines');

        if (! isset($this->draft_lines[$key])) {
            return;
        }

        $value = trim((string) $batchRefNo);
        $this->draft_lines[$key]['batch_ref_no'] = $value !== '' ? mb_substr($value, 0, 100) : null;
    }

    public function setDraftLineExpiryDate(int $key, mixed $expiryDate): void
    {
        $this->resetErrorBag('draft_lines');

        if (! isset($this->draft_lines[$key])) {
            return;
        }

        $value = trim((string) $expiryDate);
        if ($value === '') {
            $this->draft_lines[$key]['expiry_date'] = null;
            return;
        }

        try {
            $this->draft_lines[$key]['expiry_date'] = Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            $this->draft_lines[$key]['expiry_date'] = null;
        }
    }

    public function save(): void
    {
        $this->resetErrorBag();

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) (auth()->user()?->branch_id ?? 0);
        }

        if ($this->branch_id <= 0) {
            $this->addError('branch_id', 'Branch is required.');
            return;
        }

        $items = array_values($this->draft_lines);
        if (count($items) === 0) {
            $this->addError('draft_lines', 'No lines added.');
            return;
        }

        $receiptId = DB::transaction(function () use ($items) {
            $totalQty = 0;
            $totalCost = 0.0;

            foreach ($items as $row) {
                $qty = (int) ($row['quantity'] ?? 0);
                $totalQty += $qty;

                $cp = $row['cost_price'] ?? null;
                if ($cp !== null && $cp !== '') {
                    $totalCost += (float) $cp * $qty;
                }
            }

            $receipt = StockInReceipt::query()->create([
                'receipt_no' => 'SI-' . strtoupper(Str::random(10)),
                'branch_id' => (int) $this->branch_id,
                'user_id' => auth()->id(),
                'received_at' => Carbon::parse($this->received_at_date)->startOfDay(),
                'notes' => $this->notes,
                'total_quantity' => (int) $totalQty,
                'total_cost' => $totalCost > 0 ? number_format($totalCost, 2, '.', '') : null,
            ]);

            foreach ($items as $row) {
                $qty = (int) ($row['quantity'] ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $stock = ProductStock::query()->firstOrCreate(
                    ['branch_id' => (int) $this->branch_id, 'product_id' => (int) $row['product_id']],
                    ['current_stock' => 0, 'minimum_stock' => 0, 'cost_price' => null]
                );

                $stock = ProductStock::query()->whereKey($stock->id)->lockForUpdate()->firstOrFail();

                $beforeStock = (int) $stock->current_stock;
                $beforeCost = $stock->cost_price !== null ? (float) $stock->cost_price : null;

                $stock->current_stock = $beforeStock + $qty;

                $incomingCost = (($row['cost_price'] ?? null) !== null && ($row['cost_price'] ?? '') !== '')
                    ? (float) $row['cost_price']
                    : $beforeCost;

                if ($incomingCost !== null) {
                    $afterQty = $beforeStock + $qty;
                    if ($afterQty > 0) {
                        $beforeCostValue = $beforeCost !== null ? (float) $beforeCost : (float) $incomingCost;
                        $weighted = (($beforeStock * $beforeCostValue) + ($qty * $incomingCost)) / $afterQty;
                        $stock->cost_price = number_format($weighted, 2, '.', '');
                    }
                }

                $stock->save();

                // Update Product WAC
                $product = Product::query()->find((int) $row['product_id']);
                if ($product) {
                    $productWac = $product->weighted_average_cost !== null ? (float) $product->weighted_average_cost : (float) ($product->cost_price ?? 0);
                    $newCost = $incomingCost ?? $productWac;
                    $currentStock = (int) ($product->stock?->current_stock ?? 0);
                    $newStock = $currentStock + $qty;
                    
                    if ($newStock > 0) {
                        $newWac = (($currentStock * $productWac) + ($qty * $newCost)) / $newStock;
                        $product->weighted_average_cost = number_format($newWac, 2, '.', '');
                        $product->save();
                    }
                }

                $afterStock = (int) $stock->current_stock;

                $lineTotal = (($row['cost_price'] ?? null) !== null && ($row['cost_price'] ?? '') !== '')
                    ? ((float) $row['cost_price'] * $qty)
                    : null;

                StockInItem::query()->create([
                    'stock_in_receipt_id' => (int) $receipt->id,
                    'product_id' => (int) $row['product_id'],
                    'supplier_name' => ($row['supplier_name'] ?? null) ?: null,
                    'batch_ref_no' => ($row['batch_ref_no'] ?? null) ?: null,
                    'entry_mode' => (string) ($row['entry_mode'] ?? 'unit'),
                    'bulk_quantity' => $row['bulk_quantity'] ?? null,
                    'units_per_bulk' => $row['units_per_bulk'] ?? null,
                    'bulk_type_id' => $row['bulk_type_id'] ?? null,
                    'expiry_date' => ($row['expiry_date'] ?? null) ?: null,
                    'quantity' => $qty,
                    'remaining_quantity' => $qty,
                    'cost_price' => (($row['cost_price'] ?? null) !== null && ($row['cost_price'] ?? '') !== '') ? (string) $row['cost_price'] : null,
                    'line_total' => $lineTotal !== null ? number_format($lineTotal, 2, '.', '') : null,
                ]);

                StockMovement::query()->create([
                    'branch_id' => (int) $this->branch_id,
                    'product_id' => (int) $row['product_id'],
                    'user_id' => auth()->id(),
                    'movement_type' => 'IN',
                    'quantity' => $qty,
                    'before_stock' => $beforeStock,
                    'after_stock' => $afterStock,
                    'unit_cost' => (($row['cost_price'] ?? null) !== null && ($row['cost_price'] ?? '') !== '') ? (string) $row['cost_price'] : null,
                    'unit_price' => null,
                    'stock_in_receipt_id' => (int) $receipt->id,
                    'sales_receipt_id' => null,
                    'moved_at' => now(),
                    'notes' => $this->notes,
                ]);
            }

            ActivityLogger::log(
                'stock_in.posted',
                $receipt,
                'Stock in posted',
                [
                    'branch_id' => (int) $receipt->branch_id,
                    'stock_in_receipt_id' => (int) $receipt->id,
                    'total_quantity' => (int) $receipt->total_quantity,
                    'total_cost' => $receipt->total_cost !== null ? (float) $receipt->total_cost : null,
                    'lines_count' => count($items),
                ],
                (int) $receipt->branch_id
            );

            return (int) $receipt->id;
        });

        $this->draft_lines = [];
        $this->draft_seq = 0;
        $this->product_id = 0;
        $this->entry_mode = 'unit';
        $this->bulk_quantity = 1;
        $this->quantity = 1;
        $this->cost_price = null;
        $this->supplier_name = null;
        $this->batch_ref_no = null;
        $this->expiry_date = null;
        $this->notes = null;
        $this->selected_receipt_id = (int) $receiptId;

        session()->flash('status', 'Stock updated successfully.');
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new StockInTemplateExport, 'stock_in_template.xlsx');
    }

    public function importExcel(): void
    {
        $this->validate([
            'excel_file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new StockInImport, $this->excel_file);

        session()->flash('status', 'Stock in imported successfully.');
        $this->excel_file = null;
    }

    public function render()
    {
        $this->syncAuthContext();

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) (auth()->user()?->branch_id ?? 0);
            $branches = Branch::query()
                ->whereKey((int) (auth()->user()?->branch_id ?? 0))
                ->where('is_active', true)
                ->get();
        } else {
            $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();
        }

        $products = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when(trim($this->product_search) !== '', function ($q) {
                $term = '%' . trim($this->product_search) . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('category', fn ($qc) => $qc->where('name', 'like', $term));
                });
            })
            ->with('category')
            ->orderBy('name')
            ->get();

        $selectedProduct = null;
        if ($this->product_id > 0) {
            $selectedProduct = Product::query()
                ->with(['bulkType.bulkUnit', 'unitType'])
                ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
                ->find($this->product_id);
        }

        $searchableProducts = [];
        if ($this->branch_id > 0 && trim($this->product_search) !== '') {
            $searchableProducts = Product::query()
                ->where('status', Product::STATUS_ACTIVE)
                ->where('branch_id', $this->branch_id)
                ->where(function ($q) {
                    $term = '%' . trim($this->product_search) . '%';
                    $q->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('category', fn ($qc) => $qc->where('name', 'like', $term));
                })
                ->with('category')
                ->orderBy('name')
                ->limit(10)
                ->get();
        }

        $selectedReceipt = null;
        if ($this->selected_receipt_id > 0) {
            $selectedReceipt = StockInReceipt::query()
                ->with(['branch', 'user', 'items.product'])
                ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
                ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
                ->find($this->selected_receipt_id);
        }

        return view('livewire.stock-in-index', [
            'branches' => $branches,
            'products' => $products,
            'selectedProduct' => $selectedProduct,
            'searchableProducts' => $searchableProducts,
            'selectedReceipt' => $selectedReceipt,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }

    public function selectProduct(int $productId): void
    {
        $this->product_id = $productId;
        $this->product_search = '';
    }
}
