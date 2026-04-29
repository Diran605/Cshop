<?php

namespace App\Livewire;

use App\Exports\SalesTemplateExport;
use App\Imports\SalesImport;
use App\Models\Branch;
use App\Models\ClearanceItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesItem;
use App\Models\SalesReceipt;
use App\Models\StockInItem;
use App\Models\StockMovement;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class SalesIndex extends Component
{
    use WithPagination;

    public string $mode = 'add';

    public int $branch_id = 0;

    public int $product_id = 0;

    public ?array $selected_product_data = null;

    public string $product_search = '';

    public string $sales_search = '';

    public string $sales_date_from;

    public string $sales_date_to;

    public string $sales_status = 'active';

    /**
     * @var array<int>
     */
    public array $selected_sales = [];

    public string $entry_mode = 'unit';

    public int $entry_quantity = 1;

    public int $bulk_quantity = 1;

    public ?string $custom_entry_price = null; // Track custom price before adding to cart

    public bool $isSuperAdmin = false;

    public int $auth_user_id = 0;

    /**
     * @var array<int, array{product_id:int,name:string,unit_price:string,quantity:int,entry_mode:string,bulk_quantity:?int,units_per_bulk:?int,bulk_type_id:?int}>
     */
    public array $cart = [];

    public ?int $selected_batch_id = null;

    public bool $is_fifo = true;

    public string $payment_method = 'cash';

    public ?string $amount_paid = null;

    public ?string $customer_name = null;

    public ?string $notes = null;

    public string $sold_at_date = '';

    public int $selected_sale_id = 0;

    public bool $show_sale_modal = false;

    public bool $show_edit_modal = false;

    public bool $show_void_modal = false;

    public bool $confirm_pending_selection = false;

    public int $editing_sale_id = 0;

    public int $edit_branch_id = 0;

    public array $edit_cart = [];

    public int $edit_product_id = 0;

    public string $edit_entry_mode = 'unit';

    public int $edit_entry_quantity = 1;

    public int $edit_bulk_quantity = 1;

    public string $edit_payment_method = 'cash';

    public ?string $edit_amount_paid = null;

    public ?string $edit_customer_name = null;

    public ?string $edit_notes = null;

    public ?string $edit_reason = null;

    public int $pending_void_sale_id = 0;

    public ?string $void_reason = null;

    public $excel_file = null;

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'string', 'in:cash'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'sold_at_date' => ['required', 'date'],
        ];
    }

    private function getMinPriceMap(int $branchId, array $productIds): array
    {
        $ids = collect($productIds)->map(fn ($v) => (int) $v)->filter(fn ($v) => $v > 0)->unique()->values();
        if ($branchId <= 0 || $ids->isEmpty()) {
            return [];
        }

        return Product::query()
            ->where('branch_id', $branchId)
            ->whereIn('id', $ids->all())
            ->pluck('min_selling_price', 'id')
            ->map(fn ($v) => $v !== null ? (float) $v : null)
            ->all();
    }

    public function mount(string $mode = 'add'): void
    {
        $this->mode = 'add';

        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->auth_user_id = (int) ($user?->id ?? 0);

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        } else {
            $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        }

        $this->product_id = 0;
        $this->selected_product_data = null;
        $this->entry_mode = 'unit';
        $this->entry_quantity = 1;
        $this->bulk_quantity = 1;
        $this->payment_method = 'cash';
        $this->amount_paid = null;
        $this->customer_name = null;
        $this->notes = null;
        $this->selected_sale_id = 0;

        $this->sold_at_date = Carbon::today()->toDateString();

        $this->product_search = '';
        $this->sales_search = '';

        $today = Carbon::today();
        $this->sales_date_from = $today->toDateString();
        $this->sales_date_to = $today->toDateString();
        $this->sales_status = 'active';
        $this->selected_sales = [];

        $this->show_edit_modal = false;
        $this->show_void_modal = false;
        $this->editing_sale_id = 0;
        $this->edit_branch_id = 0;
        $this->edit_cart = [];
        $this->edit_product_id = 0;
        $this->edit_entry_mode = 'unit';
        $this->edit_entry_quantity = 1;
        $this->edit_bulk_quantity = 1;
        $this->edit_payment_method = 'cash';
        $this->edit_amount_paid = null;
        $this->edit_customer_name = null;
        $this->edit_notes = null;
        $this->pending_void_sale_id = 0;
        $this->void_reason = null;
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;
            $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        }
    }

    public function updatedEntryMode(string $value): void
    {
        // Reset and reinitialize custom price when switching entry mode
        $this->custom_entry_price = null;

        // Recalculate and show the appropriate price for the new mode
        if ($this->product_id > 0 && $this->selected_product_data) {
            $this->custom_entry_price = $this->entryPriceDisplay;
        }
    }

    #[\Livewire\Attributes\On('product-selected')]
    public function handleProductSelected(?int $productId, ?int $batchId = null, bool $isFifo = true): void
    {
        $this->product_id = $productId ?? 0;
        $this->selected_batch_id = $batchId;
        $this->is_fifo = $isFifo;
        $this->updatedProductId($this->product_id);
    }

    #[\Livewire\Attributes\On('batch-selected')]
    public function handleBatchSelected(int $batchId, bool $isFifo): void
    {
        $this->selected_batch_id = $batchId;
        $this->is_fifo = $isFifo;
    }

    public function updatedProductId(int $value): void
    {
        if ($value <= 0) {
            $this->selected_product_data = null;
            $this->custom_entry_price = null;

            return;
        }

        $product = Product::query()
            ->with(['bulkType', 'category', 'unitType'])
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->find($value);

        if ($product) {
            $this->selected_product_data = [
                'id' => $product->id,
                'name' => $product->name,
                'selling_price' => (string) ($product->selling_price ?? '0'),
                'min_selling_price' => $product->min_selling_price !== null ? (string) $product->min_selling_price : null,
                'bulk_enabled' => (bool) $product->bulk_enabled,
                'units_per_bulk' => (int) ($product->bulkType?->units_per_bulk ?? 0),
                'category_name' => $product->category?->name,
                'unit_type_name' => $product->unitType?->name,
            ];

            // Check for clearance quantity
            $clearanceQty = ClearanceItem::where('branch_id', $this->branch_id)
                ->where('product_id', $product->id)
                ->where('status', ClearanceItem::STATUS_ACTIONED)
                ->where('action_type', ClearanceItem::ACTION_DISCOUNT)
                ->sum('quantity');

            // Total available quantity (non-expired)
            $totalAvailable = StockInItem::query()
                ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
                ->whereNull('stock_in_receipts.voided_at')
                ->where('stock_in_receipts.branch_id', $this->branch_id)
                ->where('stock_in_items.product_id', $product->id)
                ->where('stock_in_items.remaining_quantity', '>', 0)
                ->where(function($q) {
                    $q->whereNull('stock_in_items.expiry_date')
                      ->orWhere('stock_in_items.expiry_date', '>=', now()->toDateString());
                })
                ->sum('stock_in_items.remaining_quantity');

            $this->selected_product_data['clearance_qty'] = (int) $clearanceQty;
            $this->selected_product_data['normal_qty'] = max(0, (int) $totalAvailable - (int) $clearanceQty);

            $this->custom_entry_price = null; // Reset custom price when changing product

            // Reset entry mode to unit if product doesn't support bulk
            if (! $product->bulk_enabled) {
                $this->entry_mode = 'unit';
            }
        } else {
            $this->selected_product_data = null;
            $this->custom_entry_price = null;
        }
    }

    public function updatedBranchId(int $value): void
    {
        // Reset product selection when branch changes
        $this->product_id = 0;
        $this->selected_product_data = null;

        if (! $this->isSuperAdmin) {
            return;
        }

        $this->cart = [];
        $this->selected_sale_id = 0;
    }

    public function addProduct(): void
    {
        $this->resetErrorBag('cart');

        if ($this->branch_id <= 0 || $this->product_id <= 0) {
            return;
        }

        $product = Product::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->find($this->product_id);
        if (! $product) {
            return;
        }

        $bulkTypeId = null;
        $unitsPerBulk = null;
        $bulkQty = null;
        $unitsQty = 0;

        if ($this->entry_mode === 'bulk') {
            if (! (bool) $product->bulk_enabled) {
                $this->addError('cart', 'Bulk mode is not enabled for this product.');

                return;
            }

            $product = Product::query()
                ->with(['bulkType'])
                ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
                ->find($this->product_id);
            if (! $product || ! $product->bulkType) {
                $this->addError('cart', 'Bulk type is not configured for this product.');

                return;
            }

            $bulkTypeId = (int) $product->bulkType->id;
            $unitsPerBulk = (int) $product->bulkType->units_per_bulk;
            $bulkQty = max(1, (int) $this->bulk_quantity);

            if ($unitsPerBulk <= 0) {
                $this->addError('cart', 'Invalid units per bulk configuration.');

                return;
            }

            $unitsQty = $bulkQty * $unitsPerBulk;
        } else {
            $unitsQty = max(1, (int) $this->entry_quantity);
        }

        if (isset($this->cart[$product->id])) {
            $existingMode = (string) ($this->cart[$product->id]['entry_mode'] ?? 'unit');
            if ($existingMode === 'bulk' && $unitsPerBulk) {
                $this->cart[$product->id]['bulk_quantity'] = (int) ($this->cart[$product->id]['bulk_quantity'] ?? 0) + (int) ($bulkQty ?? 1);
                $this->cart[$product->id]['quantity'] = (int) $this->cart[$product->id]['bulk_quantity'] * (int) $this->cart[$product->id]['units_per_bulk'];
            } else {
                $this->cart[$product->id]['quantity'] = (int) $this->cart[$product->id]['quantity'] + $unitsQty;
            }

            return;
        }

        // Determine unit price: use custom price if set, otherwise use product selling price
        $unitPrice = (float) $product->selling_price;
        if ($this->custom_entry_price !== null) {
            $customPrice = (float) $this->custom_entry_price;
            // If in bulk mode, user entered bulk price, convert to per-unit
            if ($this->entry_mode === 'bulk' && $unitsPerBulk > 0) {
                $unitPrice = $customPrice / $unitsPerBulk;
            } else {
                $unitPrice = $customPrice;
            }
        }

        $this->cart[$product->id] = [
            'product_id' => (int) $product->id,
            'name' => (string) $product->name,
            'unit_type_name' => $product->unitType?->name,
            'unit_price' => (string) number_format($unitPrice, 2, '.', ''),
            'quantity' => $unitsQty,
            'entry_mode' => $this->entry_mode,
            'bulk_quantity' => $bulkQty,
            'units_per_bulk' => $unitsPerBulk,
            'bulk_type_id' => $bulkTypeId,
            'min_selling_price' => $product->min_selling_price !== null ? (string) $product->min_selling_price : null,
            'is_clearance' => false,
            'clearance_price' => null,
            'original_price' => null,
            'use_clearance_price' => true,
            'is_fifo' => $this->is_fifo,
            'batch_id' => $this->selected_batch_id,
        ];

        // Clear custom price after adding
        $this->custom_entry_price = null;

        // Check for active clearance item
        $clearanceItem = ClearanceItem::where('branch_id', $this->branch_id)
            ->where('product_id', $product->id)
            ->where('status', ClearanceItem::STATUS_ACTIONED)
            ->where('action_type', ClearanceItem::ACTION_DISCOUNT)
            ->where('quantity', '>', 0)
            ->first();

        if ($clearanceItem && $clearanceItem->clearance_price) {
            $this->cart[$product->id]['is_clearance'] = true;
            $this->cart[$product->id]['clearance_price'] = (string) $clearanceItem->clearance_price;
            $this->cart[$product->id]['original_price'] = (string) $clearanceItem->original_price;
            $this->cart[$product->id]['clearance_item_id'] = $clearanceItem->id;
            // Default to use clearance price
            $this->cart[$product->id]['unit_price'] = (string) $clearanceItem->clearance_price;
        }

        // Clear selection to allow quick addition of next product
        $this->product_id = 0;
        $this->selected_product_data = null;
        $this->entry_quantity = 1;
        $this->bulk_quantity = 1;
        $this->custom_entry_price = null;
        $this->selected_batch_id = null;
        $this->confirm_pending_selection = false;

        $this->dispatch('product-added');
    }

    public function incrementItem(int $productId): void
    {
        $this->resetErrorBag('cart');

        if (! isset($this->cart[$productId])) {
            return;
        }

        $mode = (string) ($this->cart[$productId]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $this->cart[$productId]['bulk_quantity'] = (int) ($this->cart[$productId]['bulk_quantity'] ?? 0) + 1;
            $this->cart[$productId]['quantity'] = (int) $this->cart[$productId]['bulk_quantity'] * (int) ($this->cart[$productId]['units_per_bulk'] ?? 0);

            return;
        }

        $this->cart[$productId]['quantity'] = (int) $this->cart[$productId]['quantity'] + 1;
    }

    public function decrementItem(int $productId): void
    {
        $this->resetErrorBag('cart');

        if (! isset($this->cart[$productId])) {
            return;
        }

        $mode = (string) ($this->cart[$productId]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $newBulkQty = (int) ($this->cart[$productId]['bulk_quantity'] ?? 0) - 1;
            if ($newBulkQty <= 0) {
                unset($this->cart[$productId]);

                return;
            }

            $this->cart[$productId]['bulk_quantity'] = $newBulkQty;
            $this->cart[$productId]['quantity'] = $newBulkQty * (int) ($this->cart[$productId]['units_per_bulk'] ?? 0);

            return;
        }

        $newQty = (int) $this->cart[$productId]['quantity'] - 1;
        if ($newQty <= 0) {
            unset($this->cart[$productId]);

            return;
        }

        $this->cart[$productId]['quantity'] = $newQty;
    }

    public function removeItem(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    public function setQuantity(int $productId, mixed $quantity): void
    {
        $this->resetErrorBag('cart');

        if (! isset($this->cart[$productId])) {
            return;
        }

        $qty = (int) $quantity;
        $mode = (string) ($this->cart[$productId]['entry_mode'] ?? 'unit');

        if ($qty <= 0) {
            unset($this->cart[$productId]);

            return;
        }

        if ($mode === 'bulk') {
            $this->cart[$productId]['bulk_quantity'] = $qty;
            $this->cart[$productId]['quantity'] = $qty * (int) ($this->cart[$productId]['units_per_bulk'] ?? 0);

            return;
        }

        $this->cart[$productId]['quantity'] = $qty;
    }

    public function setUnitPrice(int $productId, mixed $unitPrice): void
    {
        $this->resetErrorBag('cart');

        // If product not in cart yet, store as custom price
        if (! isset($this->cart[$productId])) {
            $this->custom_entry_price = (string) $unitPrice;

            return;
        }

        $v = (float) $unitPrice;
        if ($v < 0) {
            $v = 0;
        }

        $mode = (string) ($this->cart[$productId]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            // User entered bulk price, convert to per-unit price for storage
            $unitsPerBulk = (int) ($this->cart[$productId]['units_per_bulk'] ?? 0);
            if ($unitsPerBulk > 0) {
                $v = $v / $unitsPerBulk;
            }
        }

        $this->cart[$productId]['unit_price'] = number_format($v, 2, '.', '');
    }

    public function toggleClearancePrice(int $productId): void
    {
        if (! isset($this->cart[$productId]) || ! ($this->cart[$productId]['is_clearance'] ?? false)) {
            return;
        }

        $useClearance = ! ($this->cart[$productId]['use_clearance_price'] ?? true);
        $this->cart[$productId]['use_clearance_price'] = $useClearance;

        if ($useClearance) {
            $this->cart[$productId]['unit_price'] = (string) ($this->cart[$productId]['clearance_price'] ?? 0);
        } else {
            $this->cart[$productId]['unit_price'] = (string) ($this->cart[$productId]['original_price'] ?? $this->cart[$productId]['selling_price'] ?? 0);
        }
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->custom_entry_price = null;
        $this->amount_paid = null;
        $this->customer_name = null;
        $this->notes = null;
        $this->resetErrorBag('cart');
    }

    public function finalizeSale(): void
    {
        $this->resetErrorBag('cart');

        // Check for pending selection (product selected but not added to cart)
        if ($this->product_id > 0 && ! $this->confirm_pending_selection) {
            $this->confirm_pending_selection = true;
            $this->addError('cart', 'You have a product selected that has not been added to the cart. Click "Post Sale" again to ignore this or add the product first.');
            return;
        }

        $data = $this->validate();

        $data['payment_method'] = 'cash';
        $this->payment_method = 'cash';

        if (! $this->isSuperAdmin) {
            $data['branch_id'] = (int) (auth()->user()?->branch_id ?? 0);
            $this->branch_id = (int) $data['branch_id'];
        }

        if ($this->branch_id <= 0 || count($this->cart) === 0) {
            return;
        }

        $cartItems = array_values($this->cart);

        $subTotal = 0.0;
        foreach ($cartItems as $item) {
            $subTotal += (float) $item['unit_price'] * (int) $item['quantity'];
        }

        $grandTotal = $subTotal;
        $amountPaid = $grandTotal;
        $changeDue = 0.0;

        try {
            $saleId = DB::transaction(function () use ($cartItems, $data, $subTotal, $grandTotal, $amountPaid, $changeDue) {
                $today = Carbon::today()->toDateString();

                foreach ($cartItems as $item) {
                    $available = (int) (DB::table('stock_in_items')
                        ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
                        ->whereNull('stock_in_receipts.voided_at')
                        ->where('stock_in_receipts.branch_id', (int) $data['branch_id'])
                        ->where('stock_in_items.product_id', (int) $item['product_id'])
                        ->where('stock_in_items.remaining_quantity', '>', 0)
                        ->where(function ($q) use ($today, $item) {
                            if (isset($item['is_fifo']) && ! $item['is_fifo'] && isset($item['batch_id']) && $item['batch_id'] > 0) {
                                return; // user selected specific batch, don't strictly filter expired
                            }
                            $q->whereNull('stock_in_items.expiry_date')
                                ->orWhere('stock_in_items.expiry_date', '>=', $today);
                        })
                        ->when(isset($item['is_fifo']) && ! $item['is_fifo'] && isset($item['batch_id']) && $item['batch_id'] > 0, function ($q) use ($item) {
                            $q->where('stock_in_items.id', (int) $item['batch_id']);
                        })
                        ->sum('stock_in_items.remaining_quantity'));

                    if ($available < (int) $item['quantity']) {
                        throw ValidationException::withMessages([
                            'cart' => 'Insufficient non-expired stock for '.$item['name'].'. Available: '.$available.', Requested: '.(int) $item['quantity'].'.',
                        ]);
                    }
                }

                $receipt = SalesReceipt::query()->create([
                    'receipt_no' => 'SL-'.strtoupper(Str::random(10)),
                    'branch_id' => (int) $data['branch_id'],
                    'user_id' => auth()->id(),
                    'sold_at' => Carbon::parse($this->sold_at_date)->startOfDay(),
                    'payment_method' => $data['payment_method'],
                    'customer_name' => ($data['customer_name'] ?? null) ?: null,
                    'sub_total' => (string) $subTotal,
                    'discount_total' => '0.00',
                    'grand_total' => (string) $grandTotal,
                    'cogs_total' => '0.00',
                    'profit_total' => '0.00',
                    'amount_paid' => (string) $amountPaid,
                    'change_due' => (string) $changeDue,
                    'notes' => $data['notes'] ?? null,
                ]);

                $cogsTotal = 0.0;
                $profitTotal = 0.0;

                $minMap = $this->getMinPriceMap((int) $data['branch_id'], array_map(fn ($row) => (int) ($row['product_id'] ?? 0), $cartItems));

                foreach ($cartItems as $item) {
                    $stock = ProductStock::query()
                        ->where('branch_id', (int) $data['branch_id'])
                        ->where('product_id', (int) $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if (! $stock) {
                        $stock = ProductStock::query()->create([
                            'branch_id' => (int) $data['branch_id'],
                            'product_id' => (int) $item['product_id'],
                            'current_stock' => 0,
                            'minimum_stock' => 0,
                            'cost_price' => null,
                        ]);
                    }

                    $beforeStock = (int) $stock->current_stock;

                    $stock->current_stock = (int) $stock->current_stock - (int) $item['quantity'];
                    $stock->save();

                    $afterStock = (int) $stock->current_stock;

                    $toAllocate = (int) $item['quantity'];
                    $batchRows = DB::table('stock_in_items')
                        ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
                        ->whereNull('stock_in_receipts.voided_at')
                        ->where('stock_in_receipts.branch_id', (int) $data['branch_id'])
                        ->where('stock_in_items.product_id', (int) $item['product_id'])
                        ->where('stock_in_items.remaining_quantity', '>', 0)
                        ->where(function ($q) use ($today, $item) {
                            if (isset($item['is_fifo']) && ! $item['is_fifo'] && isset($item['batch_id']) && $item['batch_id'] > 0) {
                                return; // user selected specific batch, don't strictly filter expired
                            }
                            $q->whereNull('stock_in_items.expiry_date')
                                ->orWhere('stock_in_items.expiry_date', '>=', $today);
                        })
                        ->when(isset($item['is_fifo']) && ! $item['is_fifo'] && isset($item['batch_id']) && $item['batch_id'] > 0, function ($q) use ($item) {
                            $q->where('stock_in_items.id', (int) $item['batch_id']);
                        })
                        ->orderByRaw('CASE WHEN stock_in_items.expiry_date IS NULL THEN 1 ELSE 0 END')
                        ->orderBy('stock_in_items.expiry_date')
                        ->orderBy('stock_in_items.id')
                        ->select(['stock_in_items.id', 'stock_in_items.remaining_quantity', 'stock_in_items.cost_price'])
                        ->lockForUpdate()
                        ->get();

                    $allocations = [];
                    $allocatedQty = 0;
                    $allocatedCost = 0.0;
                    $hasCost = false;

                    foreach ($batchRows as $row) {
                        if ($toAllocate <= 0) {
                            break;
                        }

                        $availableBatch = (int) ($row->remaining_quantity ?? 0);
                        if ($availableBatch <= 0) {
                            continue;
                        }

                        $take = min($toAllocate, $availableBatch);

                        DB::table('stock_in_items')
                            ->where('id', (int) $row->id)
                            ->update([
                                'remaining_quantity' => $availableBatch - $take,
                            ]);

                        $allocations[] = [
                            'stock_in_item_id' => (int) $row->id,
                            'quantity' => (int) $take,
                        ];

                        $allocatedQty += (int) $take;
                        if ($row->cost_price !== null && $row->cost_price !== '') {
                            $allocatedCost += (float) $row->cost_price * (int) $take;
                            $hasCost = true;
                        }

                        $toAllocate -= $take;
                    }

                    if ($toAllocate > 0) {
                        throw ValidationException::withMessages([
                            'cart' => 'Stock allocation failed for '.$item['name'].'. Please retry.',
                        ]);
                    }

                    $unitCostFloat = ($hasCost && $allocatedQty > 0) ? ($allocatedCost / $allocatedQty) : 0.0;
                    $unitCostStr = number_format((float) $unitCostFloat, 2, '.', '');

                    $lineTotal = (float) $item['unit_price'] * (int) $item['quantity'];
                    $lineCost = (float) $allocatedCost;
                    $lineProfit = $lineTotal - $lineCost;

                    $pid = (int) $item['product_id'];
                    $min = $minMap[$pid] ?? null;
                    $isLowProfit = $min !== null && (float) $item['unit_price'] < (float) $min;
                    $isLoss = $hasCost && (float) $item['unit_price'] < (float) $unitCostFloat;

                    $cogsTotal += $lineCost;
                    $profitTotal += $lineProfit;

                    $salesItem = SalesItem::query()->create([
                        'sales_receipt_id' => $receipt->id,
                        'product_id' => (int) $item['product_id'],
                        'entry_mode' => (string) ($item['entry_mode'] ?? 'unit'),
                        'bulk_quantity' => $item['bulk_quantity'] ?? null,
                        'units_per_bulk' => $item['units_per_bulk'] ?? null,
                        'bulk_type_id' => $item['bulk_type_id'] ?? null,
                        'quantity' => (int) $item['quantity'],
                        'unit_price' => (string) $item['unit_price'],
                        'unit_cost' => $unitCostStr,
                        'line_total' => number_format($lineTotal, 2, '.', ''),
                        'line_cost' => number_format($lineCost, 2, '.', ''),
                        'line_profit' => number_format($lineProfit, 2, '.', ''),
                        'is_low_profit' => (bool) $isLowProfit,
                        'is_loss' => (bool) $isLoss,
                    ]);

                    // Track clearance sale if applicable
                    if (! empty($item['is_clearance']) && ! empty($item['clearance_item_id'])) {
                        \App\Models\ClearanceSale::create([
                            'branch_id' => (int) $data['branch_id'],
                            'sales_item_id' => $salesItem->id,
                            'clearance_item_id' => $item['clearance_item_id'],
                            'original_price' => (float) $item['original_price'],
                            'clearance_price' => (float) $item['clearance_price'],
                            'discount_amount' => (float) $item['original_price'] - (float) $item['clearance_price'],
                            'quantity' => (int) $item['quantity'],
                        ]);

                        // Decrease clearance item quantity
                        ClearanceItem::where('id', $item['clearance_item_id'])
                            ->decrement('quantity', (int) $item['quantity']);

                        // Create clearance action record
                        \App\Models\ClearanceAction::create([
                            'branch_id' => (int) $data['branch_id'],
                            'clearance_item_id' => $item['clearance_item_id'],
                            'user_id' => auth()->id(),
                            'action_type' => 'sold',
                            'quantity' => (int) $item['quantity'],
                            'original_value' => (float) $item['original_price'] * (int) $item['quantity'],
                            'recovered_value' => (float) $item['clearance_price'] * (int) $item['quantity'],
                            'loss_value' => ((float) $item['original_price'] - (float) $item['clearance_price']) * (int) $item['quantity'],
                        ]);
                    }

                    foreach ($allocations as $alloc) {
                        DB::table('sales_item_allocations')->insert([
                            'sales_item_id' => (int) $salesItem->id,
                            'stock_in_item_id' => (int) $alloc['stock_in_item_id'],
                            'quantity' => (int) $alloc['quantity'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    StockMovement::query()->create([
                        'branch_id' => (int) $data['branch_id'],
                        'product_id' => (int) $item['product_id'],
                        'user_id' => auth()->id(),
                        'movement_type' => 'sale',
                        'quantity' => (int) $item['quantity'],
                        'before_stock' => $beforeStock,
                        'after_stock' => $afterStock,
                        'unit_cost' => $hasCost ? $unitCostStr : null,
                        'unit_price' => (string) $item['unit_price'],
                        'stock_in_receipt_id' => null,
                        'sales_receipt_id' => (int) $receipt->id,
                        'moved_at' => now(),
                        'notes' => null,
                        'clearance_flag' => false,
                        'created_by' => auth()->id(),
                    ]);
                }

                $receipt->cogs_total = number_format($cogsTotal, 2, '.', '');
                $receipt->profit_total = number_format($profitTotal, 2, '.', '');
                $receipt->save();

                ActivityLogger::log(
                    'sale.posted',
                    $receipt,
                    'Sale posted',
                    [
                        'branch_id' => (int) $receipt->branch_id,
                        'sales_receipt_id' => (int) $receipt->id,
                        'grand_total' => (float) $receipt->grand_total,
                        'amount_paid' => (float) $receipt->amount_paid,
                        'change_due' => (float) $receipt->change_due,
                        'items_count' => count($cartItems),
                    ],
                    (int) $receipt->branch_id
                );

                return (int) $receipt->id;
            });
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());

            return;
        }

        $this->clearCart();
        $this->selected_sale_id = (int) $saleId;
        session()->flash('status', 'Sale posted successfully.');
    }

    public function selectSale(int $id): void
    {
        $this->selected_sale_id = $id;
    }

    public function openSaleModal(int $id): void
    {
        $this->selected_sale_id = $id;
        $this->show_sale_modal = true;
    }

    public function closeSaleModal(): void
    {
        $this->show_sale_modal = false;
    }

    public function openEditModal(int $id): void
    {
        $sale = SalesReceipt::query()
            ->with(['items.product'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        if ($sale->voided_at) {
            return;
        }

        $this->editing_sale_id = (int) $sale->id;
        $this->edit_branch_id = (int) $sale->branch_id;
        $this->edit_payment_method = 'cash';
        $this->edit_amount_paid = $sale->amount_paid !== null ? (string) $sale->amount_paid : null;
        $this->edit_customer_name = $sale->customer_name;
        $this->edit_notes = $sale->notes;

        $this->edit_cart = [];
        foreach ($sale->items as $item) {
            $this->edit_cart[(int) $item->product_id] = [
                'product_id' => (int) $item->product_id,
                'name' => (string) ($item->product?->name ?? '-'),
                'unit_price' => (string) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'entry_mode' => (string) ($item->entry_mode ?? 'unit'),
                'bulk_quantity' => $item->bulk_quantity !== null ? (int) $item->bulk_quantity : null,
                'units_per_bulk' => $item->units_per_bulk !== null ? (int) $item->units_per_bulk : null,
                'bulk_type_id' => $item->bulk_type_id !== null ? (int) $item->bulk_type_id : null,
            ];
        }

        $this->edit_product_id = 0;
        $this->edit_entry_mode = 'unit';
        $this->edit_entry_quantity = 1;
        $this->edit_bulk_quantity = 1;

        $this->resetErrorBag();
        $this->show_edit_modal = true;
    }

    public function closeEditModal(): void
    {
        $this->show_edit_modal = false;
        $this->editing_sale_id = 0;
        $this->edit_branch_id = 0;
        $this->edit_cart = [];
        $this->edit_product_id = 0;
        $this->edit_entry_mode = 'unit';
        $this->edit_entry_quantity = 1;
        $this->edit_bulk_quantity = 1;
        $this->edit_payment_method = 'cash';
        $this->edit_amount_paid = null;
        $this->edit_customer_name = null;
        $this->edit_notes = null;
        $this->edit_reason = null;
        $this->resetErrorBag();
    }

    public function updatedEditProductId(): void
    {
        $product = Product::query()
            ->with(['bulkType'])
            ->where('status', 'active')
            ->when($this->edit_branch_id > 0, fn ($q) => $q->where('branch_id', $this->edit_branch_id))
            ->find($this->edit_product_id);

        if ($product && (bool) $product->bulk_enabled) {
            $this->edit_entry_mode = 'bulk';
            $this->edit_bulk_quantity = max(1, (int) $this->edit_bulk_quantity);
        } else {
            $this->edit_entry_mode = 'unit';
            $this->edit_entry_quantity = max(1, (int) $this->edit_entry_quantity);
        }
    }

    public function addEditProduct(): void
    {
        $this->resetErrorBag('edit_cart');

        if ($this->editing_sale_id <= 0 || $this->edit_branch_id <= 0 || $this->edit_product_id <= 0) {
            return;
        }

        $product = Product::query()
            ->with(['bulkType'])
            ->where('status', 'active')
            ->when($this->edit_branch_id > 0, fn ($q) => $q->where('branch_id', $this->edit_branch_id))
            ->find($this->edit_product_id);

        if (! $product) {
            return;
        }

        $bulkTypeId = null;
        $unitsPerBulk = null;
        $bulkQty = null;
        $unitsQty = 0;

        if ($this->edit_entry_mode === 'bulk') {
            if (! (bool) $product->bulk_enabled || ! $product->bulkType) {
                $this->addError('edit_cart', 'Bulk type is not configured for this product.');

                return;
            }

            $bulkTypeId = (int) $product->bulkType->id;
            $unitsPerBulk = (int) $product->bulkType->units_per_bulk;
            $bulkQty = max(1, (int) $this->edit_bulk_quantity);

            if ($unitsPerBulk <= 0) {
                $this->addError('edit_cart', 'Invalid units per bulk configuration.');

                return;
            }

            $unitsQty = $bulkQty * $unitsPerBulk;
        } else {
            $unitsQty = max(1, (int) $this->edit_entry_quantity);
        }

        if (isset($this->edit_cart[$product->id])) {
            $existingMode = (string) ($this->edit_cart[$product->id]['entry_mode'] ?? 'unit');
            if ($existingMode === 'bulk' && $unitsPerBulk) {
                $this->edit_cart[$product->id]['bulk_quantity'] = (int) ($this->edit_cart[$product->id]['bulk_quantity'] ?? 0) + (int) ($bulkQty ?? 1);
                $this->edit_cart[$product->id]['quantity'] = (int) $this->edit_cart[$product->id]['bulk_quantity'] * (int) $this->edit_cart[$product->id]['units_per_bulk'];
            } else {
                $this->edit_cart[$product->id]['quantity'] = (int) $this->edit_cart[$product->id]['quantity'] + $unitsQty;
            }

            return;
        }

        $this->edit_cart[$product->id] = [
            'product_id' => (int) $product->id,
            'name' => (string) $product->name,
            'unit_type_name' => $product->unitType?->name,
            'unit_price' => (string) $product->selling_price,
            'quantity' => $unitsQty,
            'entry_mode' => $this->edit_entry_mode,
            'bulk_quantity' => $bulkQty,
            'units_per_bulk' => $unitsPerBulk,
            'bulk_type_id' => $bulkTypeId,
        ];
    }

    public function incrementEditItem(int $productId): void
    {
        $this->resetErrorBag('edit_cart');

        if (! isset($this->edit_cart[$productId])) {
            return;
        }

        $mode = (string) ($this->edit_cart[$productId]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $this->edit_cart[$productId]['bulk_quantity'] = (int) ($this->edit_cart[$productId]['bulk_quantity'] ?? 0) + 1;
            $this->edit_cart[$productId]['quantity'] = (int) $this->edit_cart[$productId]['bulk_quantity'] * (int) ($this->edit_cart[$productId]['units_per_bulk'] ?? 0);

            return;
        }

        $this->edit_cart[$productId]['quantity'] = (int) $this->edit_cart[$productId]['quantity'] + 1;
    }

    public function decrementEditItem(int $productId): void
    {
        $this->resetErrorBag('edit_cart');

        if (! isset($this->edit_cart[$productId])) {
            return;
        }

        $mode = (string) ($this->edit_cart[$productId]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $newBulkQty = (int) ($this->edit_cart[$productId]['bulk_quantity'] ?? 0) - 1;
            if ($newBulkQty <= 0) {
                unset($this->edit_cart[$productId]);

                return;
            }

            $this->edit_cart[$productId]['bulk_quantity'] = $newBulkQty;
            $this->edit_cart[$productId]['quantity'] = $newBulkQty * (int) ($this->edit_cart[$productId]['units_per_bulk'] ?? 0);

            return;
        }

        $newQty = (int) ($this->edit_cart[$productId]['quantity'] ?? 0) - 1;
        if ($newQty <= 0) {
            unset($this->edit_cart[$productId]);

            return;
        }

        $this->edit_cart[$productId]['quantity'] = $newQty;
    }

    public function setEditQuantity(int $productId, mixed $quantity): void
    {
        $this->resetErrorBag('edit_cart');

        if (! isset($this->edit_cart[$productId])) {
            return;
        }

        $qty = (int) $quantity;
        if ($qty <= 0) {
            unset($this->edit_cart[$productId]);

            return;
        }

        $mode = (string) ($this->edit_cart[$productId]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $this->edit_cart[$productId]['bulk_quantity'] = $qty;
            $this->edit_cart[$productId]['quantity'] = $qty * (int) ($this->edit_cart[$productId]['units_per_bulk'] ?? 0);

            return;
        }

        $this->edit_cart[$productId]['quantity'] = $qty;
    }

    public function setEditUnitPrice(int $productId, mixed $unitPrice): void
    {
        $this->resetErrorBag('edit_cart');

        if (! isset($this->edit_cart[$productId])) {
            return;
        }

        $v = (float) $unitPrice;
        if ($v < 0) {
            $v = 0;
        }

        $mode = (string) ($this->edit_cart[$productId]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $unitsPerBulk = (int) ($this->edit_cart[$productId]['units_per_bulk'] ?? 0);
            if ($unitsPerBulk > 0) {
                $v = $v / $unitsPerBulk;
            }
        }

        $this->edit_cart[$productId]['unit_price'] = number_format($v, 2, '.', '');
    }

    public function removeEditItem(int $productId): void
    {
        unset($this->edit_cart[$productId]);
    }

    public function openVoidModal(int $id): void
    {
        $sale = SalesReceipt::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        if ($sale->voided_at) {
            return;
        }

        $this->pending_void_sale_id = (int) $sale->id;
        $this->void_reason = null;
        $this->resetErrorBag();
        $this->show_void_modal = true;
    }

    public function closeVoidModal(): void
    {
        $this->show_void_modal = false;
        $this->pending_void_sale_id = 0;
        $this->void_reason = null;
        $this->resetErrorBag();
    }

    public function confirmVoidSale(): void
    {
        $this->resetErrorBag();

        if ($this->pending_void_sale_id <= 0) {
            return;
        }

        try {
            DB::transaction(function () {
                $receipt = SalesReceipt::query()
                    ->whereKey($this->pending_void_sale_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($receipt->voided_at) {
                    return;
                }

                if (! $this->isSuperAdmin) {
                    abort_unless((int) (auth()->user()?->branch_id ?? 0) === (int) $receipt->branch_id, 403);
                }

                $receipt->load(['items']);

                foreach ($receipt->items as $item) {
                    $allocations = DB::table('sales_item_allocations')
                        ->where('sales_item_id', (int) $item->id)
                        ->get();

                    foreach ($allocations as $alloc) {
                        $batch = StockInItem::query()
                            ->whereKey((int) $alloc->stock_in_item_id)
                            ->lockForUpdate()
                            ->first();

                        if ($batch) {
                            $batch->remaining_quantity = (int) $batch->remaining_quantity + (int) $alloc->quantity;
                            $batch->save();
                        }
                    }

                    DB::table('sales_item_allocations')->where('sales_item_id', (int) $item->id)->delete();

                    $stock = ProductStock::query()->firstOrCreate(
                        ['branch_id' => (int) $receipt->branch_id, 'product_id' => (int) $item->product_id],
                        ['current_stock' => 0, 'minimum_stock' => 0, 'cost_price' => null]
                    );

                    $stock = ProductStock::query()->whereKey($stock->id)->lockForUpdate()->firstOrFail();

                    $beforeStock = (int) $stock->current_stock;
                    $stock->current_stock = $beforeStock + (int) $item->quantity;
                    $stock->save();

                    StockMovement::query()->create([
                        'branch_id' => (int) $receipt->branch_id,
                        'product_id' => (int) $item->product_id,
                        'user_id' => auth()->id(),
                        'movement_type' => 'IN',
                        'quantity' => (int) $item->quantity,
                        'before_stock' => $beforeStock,
                        'after_stock' => (int) $stock->current_stock,
                        'unit_cost' => $item->unit_cost !== null ? (string) $item->unit_cost : null,
                        'unit_price' => $item->unit_price !== null ? (string) $item->unit_price : null,
                        'stock_in_receipt_id' => null,
                        'sales_receipt_id' => (int) $receipt->id,
                        'moved_at' => now(),
                        'notes' => 'SALE VOID',
                    ]);
                }

                $receipt->voided_at = now();
                $receipt->voided_by = auth()->id();
                $receipt->void_reason = $this->void_reason;
                $receipt->save();

                ActivityLogger::log(
                    'sale.voided',
                    $receipt,
                    'Sale voided',
                    [
                        'branch_id' => (int) $receipt->branch_id,
                        'sales_receipt_id' => (int) $receipt->id,
                        'void_reason' => $receipt->void_reason,
                    ],
                    (int) $receipt->branch_id
                );
            });
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());

            return;
        }

        $this->closeVoidModal();
        session()->flash('status', 'Sale voided successfully.');
    }

    public function saveEdit(): void
    {
        $this->resetErrorBag();

        $this->edit_payment_method = 'cash';

        if ($this->editing_sale_id <= 0) {
            return;
        }

        if (! $this->edit_reason || trim($this->edit_reason) === '' || mb_strlen(trim($this->edit_reason)) < 5) {
            $this->addError('edit_reason', 'A reason for this edit is required (minimum 5 characters).');

            return;
        }

        $items = array_values($this->edit_cart);
        if (count($items) === 0) {
            $this->addError('edit_cart', 'Cart is empty.');

            return;
        }

        $subTotal = 0.0;
        foreach ($items as $item) {
            $subTotal += (float) $item['unit_price'] * (int) $item['quantity'];
        }

        $grandTotal = $subTotal;
        $amountPaid = ($this->edit_amount_paid !== null && $this->edit_amount_paid !== '') ? (float) $this->edit_amount_paid : 0.0;
        $changeDue = max(0.0, $amountPaid - $grandTotal);

        if ($amountPaid < $grandTotal) {
            $this->addError('edit_amount_paid', 'Amount paid must be greater than or equal to grand total.');

            return;
        }

        try {
            DB::transaction(function () use ($items, $subTotal, $grandTotal, $amountPaid, $changeDue) {
                $receipt = SalesReceipt::query()
                    ->whereKey($this->editing_sale_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $before = $receipt->only(['sub_total', 'grand_total', 'amount_paid', 'change_due', 'notes', 'customer_name']);

                if ($receipt->voided_at) {
                    return;
                }

                if (! $this->isSuperAdmin) {
                    abort_unless((int) (auth()->user()?->branch_id ?? 0) === (int) $receipt->branch_id, 403);
                }

                $receipt->load(['items']);

                foreach ($receipt->items as $oldItem) {
                    $allocations = DB::table('sales_item_allocations')
                        ->where('sales_item_id', (int) $oldItem->id)
                        ->get();

                    foreach ($allocations as $alloc) {
                        $batch = StockInItem::query()
                            ->whereKey((int) $alloc->stock_in_item_id)
                            ->lockForUpdate()
                            ->first();

                        if ($batch) {
                            $batch->remaining_quantity = (int) $batch->remaining_quantity + (int) $alloc->quantity;
                            $batch->save();
                        }
                    }

                    DB::table('sales_item_allocations')->where('sales_item_id', (int) $oldItem->id)->delete();

                    $stock = ProductStock::query()->firstOrCreate(
                        ['branch_id' => (int) $receipt->branch_id, 'product_id' => (int) $oldItem->product_id],
                        ['current_stock' => 0, 'minimum_stock' => 0, 'cost_price' => null]
                    );

                    $stock = ProductStock::query()->whereKey($stock->id)->lockForUpdate()->firstOrFail();

                    $beforeStock = (int) $stock->current_stock;
                    $stock->current_stock = $beforeStock + (int) $oldItem->quantity;
                    $stock->save();

                    StockMovement::query()->create([
                        'branch_id' => (int) $receipt->branch_id,
                        'product_id' => (int) $oldItem->product_id,
                        'user_id' => auth()->id(),
                        'movement_type' => 'IN',
                        'quantity' => (int) $oldItem->quantity,
                        'before_stock' => $beforeStock,
                        'after_stock' => (int) $stock->current_stock,
                        'unit_cost' => $oldItem->unit_cost !== null ? (string) $oldItem->unit_cost : null,
                        'unit_price' => $oldItem->unit_price !== null ? (string) $oldItem->unit_price : null,
                        'stock_in_receipt_id' => null,
                        'sales_receipt_id' => (int) $receipt->id,
                        'moved_at' => now(),
                        'notes' => 'SALE EDIT REVERSAL: '.trim($this->edit_reason),
                    ]);
                }

                SalesItem::query()->where('sales_receipt_id', (int) $receipt->id)->delete();

                foreach ($items as $item) {
                    $today = Carbon::today()->toDateString();
                    $available = (int) (DB::table('stock_in_items')
                        ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
                        ->whereNull('stock_in_receipts.voided_at')
                        ->where('stock_in_receipts.branch_id', (int) $receipt->branch_id)
                        ->where('stock_in_items.product_id', (int) $item['product_id'])
                        ->where('stock_in_items.remaining_quantity', '>', 0)
                        ->where(function ($q) use ($today) {
                            $q->whereNull('stock_in_items.expiry_date')
                                ->orWhere('stock_in_items.expiry_date', '>=', $today);
                        })
                        ->sum('stock_in_items.remaining_quantity'));

                    if ($available < (int) $item['quantity']) {
                        throw ValidationException::withMessages([
                            'edit_cart' => 'Insufficient non-expired stock for '.$item['name'].'. Available: '.$available.', Requested: '.(int) $item['quantity'].'.',
                        ]);
                    }
                }

                $cogsTotal = 0.0;
                $profitTotal = 0.0;

                $today = Carbon::today()->toDateString();

                $minMap = $this->getMinPriceMap((int) $receipt->branch_id, array_map(fn ($row) => (int) ($row['product_id'] ?? 0), $items));

                foreach ($items as $item) {
                    $stock = ProductStock::query()
                        ->where('branch_id', (int) $receipt->branch_id)
                        ->where('product_id', (int) $item['product_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $beforeStock = (int) $stock->current_stock;

                    $stock->current_stock = (int) $stock->current_stock - (int) $item['quantity'];
                    $stock->save();

                    $afterStock = (int) $stock->current_stock;

                    $toAllocate = (int) $item['quantity'];
                    $batchRows = DB::table('stock_in_items')
                        ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
                        ->whereNull('stock_in_receipts.voided_at')
                        ->where('stock_in_receipts.branch_id', (int) $receipt->branch_id)
                        ->where('stock_in_items.product_id', (int) $item['product_id'])
                        ->where('stock_in_items.remaining_quantity', '>', 0)
                        ->where(function ($q) use ($today) {
                            $q->whereNull('stock_in_items.expiry_date')
                                ->orWhere('stock_in_items.expiry_date', '>=', $today);
                        })
                        ->orderByRaw('CASE WHEN stock_in_items.expiry_date IS NULL THEN 1 ELSE 0 END')
                        ->orderBy('stock_in_items.expiry_date')
                        ->orderBy('stock_in_items.id')
                        ->select(['stock_in_items.id', 'stock_in_items.remaining_quantity', 'stock_in_items.cost_price'])
                        ->lockForUpdate()
                        ->get();

                    $allocations = [];
                    $allocatedQty = 0;
                    $allocatedCost = 0.0;
                    $hasCost = false;

                    foreach ($batchRows as $row) {
                        if ($toAllocate <= 0) {
                            break;
                        }

                        $availableBatch = (int) ($row->remaining_quantity ?? 0);
                        if ($availableBatch <= 0) {
                            continue;
                        }

                        $take = min($toAllocate, $availableBatch);

                        DB::table('stock_in_items')
                            ->where('id', (int) $row->id)
                            ->update([
                                'remaining_quantity' => $availableBatch - $take,
                            ]);

                        $allocations[] = [
                            'stock_in_item_id' => (int) $row->id,
                            'quantity' => (int) $take,
                        ];

                        $allocatedQty += (int) $take;
                        if ($row->cost_price !== null && $row->cost_price !== '') {
                            $allocatedCost += (float) $row->cost_price * (int) $take;
                            $hasCost = true;
                        }

                        $toAllocate -= $take;
                    }

                    if ($toAllocate > 0) {
                        throw ValidationException::withMessages([
                            'edit_cart' => 'Stock allocation failed for '.$item['name'].'. Please retry.',
                        ]);
                    }

                    $unitCostFloat = ($hasCost && $allocatedQty > 0) ? ($allocatedCost / $allocatedQty) : 0.0;
                    $unitCostStr = number_format((float) $unitCostFloat, 2, '.', '');

                    $lineTotal = (float) $item['unit_price'] * (int) $item['quantity'];
                    $lineCost = (float) $allocatedCost;
                    $lineProfit = $lineTotal - $lineCost;

                    $pid = (int) $item['product_id'];
                    $min = $minMap[$pid] ?? null;
                    $isLowProfit = $min !== null && (float) $item['unit_price'] < (float) $min;
                    $isLoss = $hasCost && (float) $item['unit_price'] < (float) $unitCostFloat;

                    $cogsTotal += $lineCost;
                    $profitTotal += $lineProfit;

                    $salesItem = SalesItem::query()->create([
                        'sales_receipt_id' => (int) $receipt->id,
                        'product_id' => (int) $item['product_id'],
                        'entry_mode' => (string) ($item['entry_mode'] ?? 'unit'),
                        'bulk_quantity' => $item['bulk_quantity'] ?? null,
                        'units_per_bulk' => $item['units_per_bulk'] ?? null,
                        'bulk_type_id' => $item['bulk_type_id'] ?? null,
                        'quantity' => (int) $item['quantity'],
                        'unit_price' => (string) $item['unit_price'],
                        'unit_cost' => $unitCostStr,
                        'line_total' => number_format($lineTotal, 2, '.', ''),
                        'line_cost' => number_format($lineCost, 2, '.', ''),
                        'line_profit' => number_format($lineProfit, 2, '.', ''),
                        'is_low_profit' => (bool) $isLowProfit,
                        'is_loss' => (bool) $isLoss,
                    ]);

                    foreach ($allocations as $alloc) {
                        DB::table('sales_item_allocations')->insert([
                            'sales_item_id' => (int) $salesItem->id,
                            'stock_in_item_id' => (int) $alloc['stock_in_item_id'],
                            'quantity' => (int) $alloc['quantity'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }

                    StockMovement::query()->create([
                        'branch_id' => (int) $receipt->branch_id,
                        'product_id' => (int) $item['product_id'],
                        'user_id' => auth()->id(),
                        'movement_type' => 'OUT',
                        'quantity' => (int) $item['quantity'],
                        'before_stock' => $beforeStock,
                        'after_stock' => $afterStock,
                        'unit_cost' => $hasCost ? $unitCostStr : null,
                        'unit_price' => (string) $item['unit_price'],
                        'stock_in_receipt_id' => null,
                        'sales_receipt_id' => (int) $receipt->id,
                        'moved_at' => now(),
                        'notes' => 'SALE EDIT: '.trim($this->edit_reason),
                    ]);
                }

                $receipt->payment_method = (string) $this->edit_payment_method;
                $receipt->sub_total = (string) $subTotal;
                $receipt->discount_total = '0.00';
                $receipt->grand_total = (string) $grandTotal;
                $receipt->cogs_total = number_format($cogsTotal, 2, '.', '');
                $receipt->profit_total = number_format($profitTotal, 2, '.', '');
                $receipt->amount_paid = (string) $amountPaid;
                $receipt->change_due = (string) $changeDue;
                $receipt->notes = $this->edit_notes;
                $receipt->save();

                ActivityLogger::log(
                    'sale.updated',
                    $receipt,
                    'Sale updated',
                    [
                        'branch_id' => (int) $receipt->branch_id,
                        'sales_receipt_id' => (int) $receipt->id,
                        'before' => $before,
                        'after' => $receipt->only(['sub_total', 'grand_total', 'amount_paid', 'change_due', 'notes', 'customer_name']),
                        'items_count' => count($items),
                        'edit_reason' => trim($this->edit_reason),
                    ],
                    (int) $receipt->branch_id
                );
            });
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());

            return;
        }

        $this->closeEditModal();
        session()->flash('status', 'Sale updated successfully.');
    }

    public function clearSelectedSales(): void
    {
        $this->selected_sales = [];
    }

    public function selectAllSalesForDay(string $day): void
    {
        $from = Carbon::parse($day)->startOfDay();
        $to = Carbon::parse($day)->endOfDay();

        $q = SalesReceipt::query()
            ->whereBetween('sold_at', [$from, $to])
            ->when(! $this->isSuperAdmin, fn ($qq) => $qq->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($qq) => $qq->where('branch_id', $this->branch_id));

        if ($this->sales_status === 'active') {
            $q->whereNull('voided_at');
        } elseif ($this->sales_status === 'voided') {
            $q->whereNotNull('voided_at');
        }

        if (trim($this->sales_search) !== '') {
            $term = '%'.trim($this->sales_search).'%';
            $q->where(function ($qq) use ($term) {
                $qq->where('receipt_no', 'like', $term)
                    ->orWhere('customer_name', 'like', $term)
                    ->orWhereHas('branch', fn ($qb) => $qb->where('name', 'like', $term))
                    ->orWhereHas('user', fn ($qu) => $qu->where('name', 'like', $term));
            });
        }

        $this->selected_sales = $q->orderByDesc('sold_at')->pluck('id')->map(fn ($v) => (int) $v)->all();
    }

    public function downloadTemplate(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        return Excel::download(new SalesTemplateExport, 'sales_template.xlsx');
    }

    public function importExcel(): void
    {
        $this->validate([
            'excel_file' => 'required|mimes:xlsx,xls',
        ]);

        Excel::import(new SalesImport, $this->excel_file);

        session()->flash('status', 'Sales imported successfully.');
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
                $term = '%'.trim($this->product_search).'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('category', fn ($qc) => $qc->where('name', 'like', $term));
                });
            })
            ->with('category')
            ->orderBy('name')
            ->get();

        $editProducts = Product::query()
            ->with(['bulkType.bulkUnit', 'unitType'])
            ->where('status', Product::STATUS_ACTIVE)
            ->when(($this->edit_branch_id > 0), fn ($q) => $q->where('branch_id', $this->edit_branch_id))
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
                    $term = '%'.trim($this->product_search).'%';
                    $q->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('category', fn ($qc) => $qc->where('name', 'like', $term));
                })
                ->with('category')
                ->orderBy('name')
                ->limit(10)
                ->get();
        }

        $stockMap = ProductStock::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->pluck('current_stock', 'product_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $editStockMap = ProductStock::query()
            ->when($this->edit_branch_id > 0, fn ($q) => $q->where('branch_id', $this->edit_branch_id))
            ->pluck('current_stock', 'product_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $cartItems = array_values($this->cart);
        $subTotal = 0.0;
        foreach ($cartItems as $item) {
            $subTotal += (float) $item['unit_price'] * (int) $item['quantity'];
        }
        $grandTotal = $subTotal;
        $amountPaid = ($this->amount_paid !== null && $this->amount_paid !== '') ? (float) $this->amount_paid : 0.0;
        $changeDue = max(0.0, $amountPaid - $grandTotal);

        $editCartItems = array_values($this->edit_cart);
        $editSubTotal = 0.0;
        foreach ($editCartItems as $item) {
            $editSubTotal += (float) $item['unit_price'] * (int) $item['quantity'];
        }
        $editGrandTotal = $editSubTotal;
        $editAmountPaid = ($this->edit_amount_paid !== null && $this->edit_amount_paid !== '') ? (float) $this->edit_amount_paid : 0.0;
        $editChangeDue = max(0.0, $editAmountPaid - $editGrandTotal);

        $sales = SalesReceipt::query()
            ->with(['branch', 'user'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when(trim($this->sales_search) !== '', function ($q) {
                $term = '%'.trim($this->sales_search).'%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('receipt_no', 'like', $term)
                        ->orWhere('customer_name', 'like', $term)
                        ->orWhereHas('branch', fn ($qb) => $qb->where('name', 'like', $term))
                        ->orWhereHas('user', fn ($qu) => $qu->where('name', 'like', $term));
                });
            })
            ->when($this->sales_date_from !== '' && $this->sales_date_to !== '', function ($q) {
                $from = Carbon::parse($this->sales_date_from)->startOfDay();
                $to = Carbon::parse($this->sales_date_to)->endOfDay();
                $q->whereBetween('sold_at', [$from, $to]);
            })
            ->when($this->sales_status === 'active', fn ($q) => $q->whereNull('voided_at'))
            ->when($this->sales_status === 'voided', fn ($q) => $q->whereNotNull('voided_at'))
            ->orderByDesc('sold_at')
            ->paginate(20);

        $selectedSale = null;
        if ($this->selected_sale_id > 0) {
            $selectedSale = SalesReceipt::query()
                ->with(['branch', 'user', 'items.product'])
                ->find($this->selected_sale_id);
        }

        $selectedBranch = $branches->firstWhere('id', $this->branch_id);

        return view('livewire.sales-index', [
            'branches' => $branches,
            'products' => $products,
            'editProducts' => $editProducts,
            'selectedProduct' => $selectedProduct,
            'searchableProducts' => $searchableProducts,
            'stockMap' => $stockMap,
            'editStockMap' => $editStockMap,
            'cartItems' => $cartItems,
            'subTotal' => $subTotal,
            'grandTotal' => $grandTotal,
            'changeDue' => $changeDue,
            'editCartItems' => $editCartItems,
            'editSubTotal' => $editSubTotal,
            'editGrandTotal' => $editGrandTotal,
            'editChangeDue' => $editChangeDue,
            'sales' => $sales,
            'selectedSale' => $selectedSale,
            'selectedBranch' => $selectedBranch,
            'entryPriceDisplay' => $this->entryPriceDisplay,
        ]);
    }

    #[Computed]
    protected function entryPriceDisplay(): string
    {
        if ($this->product_id <= 0 || ! $this->selected_product_data) {
            return '0';
        }

        // If user entered a custom price before adding to cart, use that
        if ($this->custom_entry_price !== null) {
            return $this->custom_entry_price;
        }

        $isBulk = $this->entry_mode === 'bulk' && $this->selected_product_data['bulk_enabled'];
        $unitsPerBulk = (int) ($this->selected_product_data['units_per_bulk'] ?? 0);

        if ($isBulk && $unitsPerBulk > 0) {
            $basePrice = (float) ($this->cart[$this->product_id]['unit_price'] ?? $this->selected_product_data['selling_price'] ?? 0);

            return number_format($basePrice * $unitsPerBulk, 2, '.', '');
        }

        return (string) ($this->cart[$this->product_id]['unit_price'] ?? $this->selected_product_data['selling_price'] ?? '0');
    }

    public function selectProduct(int $productId): void
    {
        $this->product_id = $productId;
        $this->product_search = '';

        if ($productId <= 0) {
            $this->selected_product_data = null;
            $this->custom_entry_price = null;

            return;
        }

        $product = Product::query()
            ->with(['bulkType.bulkUnit', 'unitType'])
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->find($productId);

        if ($product) {
            $this->selected_product_data = [
                'id' => $product->id,
                'name' => $product->name,
                'selling_price' => (string) ($product->selling_price ?? '0'),
                'bulk_enabled' => (bool) ($product->bulk_enabled ?? false),
                'units_per_bulk' => (int) ($product->units_per_bulk ?? 0),
                'bulk_type_id' => $product->bulk_type_id,
            ];

            if ((bool) $product->bulk_enabled && $this->entry_mode === 'bulk') {
                $this->entry_mode = 'unit';
            }

            // Initialize custom_entry_price with the calculated display price
            $this->custom_entry_price = $this->entryPriceDisplay;
        } else {
            $this->selected_product_data = null;
            $this->custom_entry_price = null;
        }
    }
}
