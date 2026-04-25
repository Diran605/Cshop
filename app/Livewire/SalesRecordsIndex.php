<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesItem;
use App\Models\SalesReceipt;
use App\Models\StockAdjustment;
use App\Models\StockInItem;
use App\Models\StockMovement;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class SalesRecordsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public int $branch_id = 0;
    public string $date_from = '';
    public string $date_to = '';
    public string $payment_filter = 'all';
    public string $status_filter = 'all';

    public bool $isSuperAdmin = false;
    public int $auth_user_id = 0;

    /**
     * @var array<int>
     */
    public array $selected_sales = [];

    // View modal
    public bool $show_view_modal = false;
    public ?SalesReceipt $viewing_receipt = null;

    // Edit modal
    public bool $show_edit_modal = false;
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

    // Void modal
    public bool $show_void_modal = false;
    public int $pending_void_sale_id = 0;
    public ?string $void_reason = null;

    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        return [
            'edit_payment_method' => ['required', 'string', 'in:cash'],
            'edit_amount_paid' => ['required', 'numeric', 'min:0'],
            'edit_customer_name' => ['nullable', 'string', 'max:255'],
            'edit_notes' => ['nullable', 'string', 'max:1000'],
            'void_reason' => ['required', 'string', 'min:10', 'max:500'],
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
            $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        }

        $this->date_from = '';
        $this->date_to = '';
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;
        }

        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user->branch_id ?? 0);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBranchId(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    // ==================== SELECTION ====================

    public function clearSelectedSales(): void
    {
        $this->selected_sales = [];
    }

    public function selectAllSalesForDay(string $day): void
    {
        if (! auth()->user()?->can('sales_records.batch_print')) {
            $this->dispatch('banner-message', message: 'You do not have permission to batch print sales records.', style: 'danger');
            return;
        }

        $from = Carbon::parse($day)->startOfDay();
        $to = Carbon::parse($day)->endOfDay();

        $q = SalesReceipt::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->whereNull('voided_at')
            ->whereBetween('sold_at', [$from, $to]);

        $this->selected_sales = $q->orderByDesc('sold_at')->pluck('id')->map(fn ($v) => (int) $v)->all();
    }

    // ==================== VIEW ====================

    public function viewSale(int $saleId): void
    {
        $this->syncAuthContext();

        $receipt = SalesReceipt::query()
            ->with(['branch', 'user', 'voidedBy', 'items.product'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->findOrFail($saleId);

        $this->viewing_receipt = $receipt;
        $this->show_view_modal = true;
    }

    public function closeViewModal(): void
    {
        $this->show_view_modal = false;
        $this->viewing_receipt = null;
    }

    // ==================== EDIT ====================

    public function openEditModal(int $saleId): void
    {
        $this->syncAuthContext();

        if (! auth()->user()?->can('sales_records.edit')) {
            $this->dispatch('banner-message', message: 'You do not have permission to edit sales records.', style: 'danger');
            return;
        }

        $sale = SalesReceipt::query()
            ->with(['items.product'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->findOrFail($saleId);

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

        $this->edit_cart[$productId]['unit_price'] = number_format($v, 2, '.', '');
    }

    public function removeEditItem(int $productId): void
    {
        unset($this->edit_cart[$productId]);
    }

    public function saveEdit(): void
    {
        $this->resetErrorBag();

        if (! auth()->user()?->can('sales_records.edit')) {
            $this->dispatch('banner-message', message: 'You do not have permission to edit sales records.', style: 'danger');
            return;
        }

        $this->validate([
            'edit_reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $this->edit_payment_method = 'cash';

        if ($this->editing_sale_id <= 0) {
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

                if ($receipt->voided_at) {
                    return;
                }

                if (! $this->isSuperAdmin) {
                    abort_unless((int) (auth()->user()?->branch_id ?? 0) === (int) $receipt->branch_id, 403);
                }

                $receipt->load(['items']);

                // Reverse old items
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

                    $stock = ProductStock::query()
                        ->where('branch_id', (int) $receipt->branch_id)
                        ->where('product_id', (int) $item->product_id)
                        ->first();

                    if ($stock) {
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
                            'sales_receipt_id' => (int) $receipt->id,
                            'moved_at' => now(),
                            'notes' => 'SALE EDIT REVERSE: ' . $this->edit_reason,
                        ]);
                    }
                }

                $receipt->items()->delete();

                // Add new items
                $cogsTotal = 0.0;
                $profitTotal = 0.0;

                foreach ($items as $item) {
                    $product = Product::query()->find((int) $item['product_id']);
                    $unitCost = $product?->cost_price !== null ? (float) $product->cost_price : 0.0;
                    $lineTotal = (float) $item['unit_price'] * (int) $item['quantity'];
                    $lineCost = $unitCost * (int) $item['quantity'];
                    $lineProfit = $lineTotal - $lineCost;

                    $salesItem = SalesItem::query()->create([
                        'sales_receipt_id' => (int) $receipt->id,
                        'product_id' => (int) $item['product_id'],
                        'entry_mode' => (string) ($item['entry_mode'] ?? 'unit'),
                        'bulk_quantity' => $item['bulk_quantity'] ?? null,
                        'units_per_bulk' => $item['units_per_bulk'] ?? null,
                        'bulk_type_id' => $item['bulk_type_id'] ?? null,
                        'quantity' => (int) $item['quantity'],
                        'unit_price' => (string) $item['unit_price'],
                        'unit_cost' => $unitCost > 0 ? number_format($unitCost, 2, '.', '') : null,
                        'line_total' => number_format($lineTotal, 2, '.', ''),
                        'line_cost' => number_format($lineCost, 2, '.', ''),
                        'line_profit' => number_format($lineProfit, 2, '.', ''),
                        'is_low_profit' => $lineProfit > 0 && $lineProfit < ($lineTotal * 0.1),
                        'is_loss' => $lineProfit < 0,
                    ]);

                    // Allocate stock
                    $stock = ProductStock::query()
                        ->where('branch_id', (int) $receipt->branch_id)
                        ->where('product_id', (int) $item['product_id'])
                        ->first();

                    if ($stock) {
                        $beforeStock = (int) $stock->current_stock;
                        $stock->current_stock = max(0, $beforeStock - (int) $item['quantity']);
                        $stock->save();

                        StockMovement::query()->create([
                            'branch_id' => (int) $receipt->branch_id,
                            'product_id' => (int) $item['product_id'],
                            'user_id' => auth()->id(),
                            'movement_type' => 'OUT',
                            'quantity' => (int) $item['quantity'],
                            'before_stock' => $beforeStock,
                            'after_stock' => (int) $stock->current_stock,
                            'unit_cost' => $unitCost > 0 ? number_format($unitCost, 2, '.', '') : null,
                            'unit_price' => (string) $item['unit_price'],
                            'sales_receipt_id' => (int) $receipt->id,
                            'moved_at' => now(),
                            'notes' => 'SALE EDIT: ' . $this->edit_reason,
                        ]);
                    }

                    $cogsTotal += $lineCost;
                    $profitTotal += $lineProfit;
                }

                $receipt->sub_total = number_format($subTotal, 2, '.', '');
                $receipt->discount_total = '0.00';
                $receipt->grand_total = number_format($grandTotal, 2, '.', '');
                $receipt->cogs_total = number_format($cogsTotal, 2, '.', '');
                $receipt->profit_total = number_format($profitTotal, 2, '.', '');
                $receipt->amount_paid = number_format($amountPaid, 2, '.', '');
                $receipt->change_due = number_format($changeDue, 2, '.', '');
                $receipt->customer_name = $this->edit_customer_name;
                $receipt->notes = $this->edit_notes;
                $receipt->save();

                ActivityLogger::log(
                    'sale.updated',
                    $receipt,
                    'Sale updated',
                    [
                        'branch_id' => (int) $receipt->branch_id,
                        'sales_receipt_id' => (int) $receipt->id,
                        'edit_reason' => $this->edit_reason,
                    ],
                    (int) $receipt->branch_id
                );
            });
        } catch (\Exception $e) {
            $this->addError('edit_cart', 'Failed to update sale: ' . $e->getMessage());
            return;
        }

        $this->closeEditModal();
        session()->flash('status', 'Sale updated successfully.');
    }

    // ==================== VOID ====================

    public function openVoidModal(int $saleId): void
    {
        $this->syncAuthContext();

        if (! auth()->user()?->can('sales_records.void')) {
            $this->dispatch('banner-message', message: 'You do not have permission to void sales records.', style: 'danger');
            return;
        }

        $sale = SalesReceipt::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->findOrFail($saleId);

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

        if (! auth()->user()?->can('sales_records.void')) {
            $this->dispatch('banner-message', message: 'You do not have permission to void sales records.', style: 'danger');
            return;
        }

        if ($this->pending_void_sale_id <= 0) {
            return;
        }

        $this->validate(['void_reason' => ['required', 'string', 'min:10', 'max:500']]);

        try {
            DB::transaction(function () {
                $receipt = SalesReceipt::query()
                    ->whereKey($this->pending_void_sale_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($receipt->voided_at || $receipt->void_requested_at) {
                    return;
                }

                if (! $this->isSuperAdmin) {
                    abort_unless((int) (auth()->user()?->branch_id ?? 0) === (int) $receipt->branch_id, 403);
                }

                $receipt->load(['items']);

                // Create pending stock adjustments for each item (to restore stock)
                foreach ($receipt->items as $item) {
                    $stock = ProductStock::query()
                        ->where('branch_id', (int) $receipt->branch_id)
                        ->where('product_id', (int) $item->product_id)
                        ->first();

                    $currentStock = $stock ? (int) $stock->current_stock : 0;
                    $restoreQty = (int) $item->quantity;
                    $targetStock = $currentStock + $restoreQty;

                    StockAdjustment::query()->create([
                        'branch_id' => (int) $receipt->branch_id,
                        'product_id' => (int) $item->product_id,
                        'adjustment_type' => StockAdjustment::TYPE_SALES_VOID,
                        'current_stock' => $currentStock,
                        'adjustment_quantity' => abs($restoreQty),
                        'target_stock' => $targetStock,
                        'status' => StockAdjustment::STATUS_PENDING,
                        'reason' => $this->void_reason,
                        'requested_by' => auth()->id(),
                        'source_type' => 'sales_receipt',
                        'source_id' => (int) $receipt->id,
                    ]);
                }

                // Mark receipt as void pending
                $receipt->void_requested_at = now();
                $receipt->void_requested_by = auth()->id();
                $receipt->void_reason = $this->void_reason;
                $receipt->save();

                ActivityLogger::log(
                    'sale.void_requested',
                    $receipt,
                    'Sale void requested',
                    [
                        'branch_id' => (int) $receipt->branch_id,
                        'sales_receipt_id' => (int) $receipt->id,
                        'customer_name' => $receipt->customer_name,
                        'void_reason' => $this->void_reason,
                    ],
                    (int) $receipt->branch_id
                );
            });
        } catch (\Exception $e) {
            $this->addError('void_reason', 'Failed to request void: ' . $e->getMessage());
            return;
        }

        $this->closeVoidModal();
        session()->flash('status', 'Void request submitted. Awaiting manager approval.');
    }

    // ==================== RENDER ====================

    public function render()
    {
        $this->syncAuthContext();

        $query = SalesReceipt::query()
            ->with(['branch', 'user'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->status_filter === 'active', fn ($q) => $q->whereNull('voided_at')->whereNull('void_requested_at'))
            ->when($this->status_filter === 'void_pending', fn ($q) => $q->whereNotNull('void_requested_at')->whereNull('voided_at'))
            ->when($this->status_filter === 'voided', fn ($q) => $q->whereNotNull('voided_at'))
            ->when($this->payment_filter !== 'all', fn ($q) => $q->where('payment_method', $this->payment_filter))
            ->when($this->date_from, fn ($q) => $q->whereDate('sold_at', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('sold_at', '<=', $this->date_to))
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('receipt_no', 'like', '%' . $this->search . '%')
                        ->orWhere('customer_name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('sold_at', 'desc');

        $receipts = $query->paginate(20);

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $editProducts = collect();
        if ($this->show_edit_modal && $this->edit_branch_id > 0) {
            $editProducts = Product::query()
                ->with(['bulkType.bulkUnit', 'unitType'])
                ->where('branch_id', (int) $this->edit_branch_id)
                ->where('status', 'active')
                ->orderBy('name')
                ->get();
        }

        return view('livewire.sales-records-index', [
            'receipts' => $receipts,
            'branches' => $branches,
            'editProducts' => $editProducts,
        ]);
    }
}
