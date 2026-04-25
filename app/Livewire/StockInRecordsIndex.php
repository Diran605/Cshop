<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockAdjustment;
use App\Models\StockInItem;
use App\Models\StockInReceipt;
use App\Models\StockMovement;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StockInRecordsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public int $branch_id = 0;
    public string $date_from = '';
    public string $date_to = '';
    public string $status_filter = 'active';

    public bool $isSuperAdmin = false;
    public int $auth_user_id = 0;

    /**
     * @var array<int>
     */
    public array $selected_receipts = [];

    // View modal
    public bool $show_view_modal = false;
    public ?StockInReceipt $viewing_receipt = null;

    // Edit modal
    public bool $show_edit_modal = false;
    public int $editing_receipt_id = 0;
    public int $edit_branch_id = 0;
    public array $edit_cart = [];
    public int $edit_product_id = 0;
    public string $edit_entry_mode = 'unit';
    public int $edit_bulk_quantity = 1;
    public int $edit_quantity = 1;
    public ?string $edit_cost_price = null;
    public ?string $edit_supplier_name = null;
    public ?string $edit_batch_ref_no = null;
    public ?string $edit_expiry_date = null;
    public ?string $edit_notes = null;
    public ?string $edit_reason = null;
    public string $edit_received_at_date = '';

    // Void modal
    public bool $show_void_modal = false;
    public int $pending_void_receipt_id = 0;
    public ?string $void_reason = null;

    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        return [
            'edit_cost_price' => ['nullable', 'numeric', 'min:0'],
            'edit_supplier_name' => ['nullable', 'string', 'max:255'],
            'edit_batch_ref_no' => ['nullable', 'string', 'max:100'],
            'edit_expiry_date' => ['nullable', 'date'],
            'edit_notes' => ['nullable', 'string', 'max:1000'],
            'edit_received_at_date' => ['required', 'date'],
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

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    // ==================== SELECTION ====================

    public function clearSelectedReceipts(): void
    {
        $this->selected_receipts = [];
    }

    public function selectAllReceiptsForDay(string $day): void
    {
        if (! auth()->user()?->can('stock_in.batch_print')) {
            $this->dispatch('banner-message', message: 'You do not have permission to batch print stock in records.', style: 'danger');
            return;
        }

        $from = Carbon::parse($day)->startOfDay();
        $to = Carbon::parse($day)->endOfDay();

        $q = StockInReceipt::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->whereNull('voided_at')
            ->whereBetween('received_at', [$from, $to]);

        $this->selected_receipts = $q->orderByDesc('received_at')->pluck('id')->map(fn ($v) => (int) $v)->all();
    }

    // ==================== VIEW ====================

    public function viewReceipt(int $receiptId): void
    {
        $this->syncAuthContext();

        $receipt = StockInReceipt::query()
            ->with(['branch', 'user', 'voidedBy', 'items.product', 'items.product.unitType'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->findOrFail($receiptId);

        $this->viewing_receipt = $receipt;
        $this->show_view_modal = true;
    }

    public function closeViewModal(): void
    {
        $this->show_view_modal = false;
        $this->viewing_receipt = null;
    }

    // ==================== EDIT ====================

    public function openEditModal(int $receiptId): void
    {
        $this->syncAuthContext();

        if (! auth()->user()?->can('stock_in.edit')) {
            $this->dispatch('banner-message', message: 'You do not have permission to edit stock in records.', style: 'danger');
            return;
        }

        $receipt = StockInReceipt::query()
            ->with(['items.product', 'items.product.unitType'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->findOrFail($receiptId);

        if ($receipt->voided_at) {
            return;
        }

        $this->editing_receipt_id = (int) $receipt->id;
        $this->edit_branch_id = (int) $receipt->branch_id;
        $this->edit_notes = $receipt->notes;
        $this->edit_received_at_date = Carbon::parse($receipt->received_at)->toDateString();

        $this->edit_cart = [];
        foreach ($receipt->items as $item) {
            $this->edit_cart[(int) $item->id] = [
                'id' => (int) $item->id,
                'product_id' => (int) $item->product_id,
                'name' => (string) ($item->product?->name ?? '-'),
                'unit_type_name' => $item->product?->unitType?->name,
                'quantity' => (int) $item->quantity,
                'remaining_quantity' => (int) $item->remaining_quantity,
                'cost_price' => $item->cost_price !== null ? (string) $item->cost_price : null,
                'supplier_name' => $item->supplier_name,
                'batch_ref_no' => $item->batch_ref_no,
                'expiry_date' => $item->expiry_date !== null ? Carbon::parse($item->expiry_date)->toDateString() : null,
            ];
        }

        $this->edit_product_id = 0;
        $this->edit_entry_mode = 'unit';
        $this->edit_quantity = 1;
        $this->edit_bulk_quantity = 1;
        $this->edit_cost_price = null;
        $this->edit_supplier_name = null;
        $this->edit_batch_ref_no = null;
        $this->edit_expiry_date = null;

        $this->resetErrorBag();
        $this->show_edit_modal = true;
    }

    public function closeEditModal(): void
    {
        $this->show_edit_modal = false;
        $this->editing_receipt_id = 0;
        $this->edit_branch_id = 0;
        $this->edit_cart = [];
        $this->edit_product_id = 0;
        $this->edit_entry_mode = 'unit';
        $this->edit_quantity = 1;
        $this->edit_bulk_quantity = 1;
        $this->edit_cost_price = null;
        $this->edit_supplier_name = null;
        $this->edit_batch_ref_no = null;
        $this->edit_expiry_date = null;
        $this->edit_notes = null;
        $this->edit_reason = null;
        $this->edit_received_at_date = '';
        $this->resetErrorBag();
    }

    public function setEditItemQuantity(int $itemId, mixed $quantity): void
    {
        $this->resetErrorBag('edit_cart');

        if (! isset($this->edit_cart[$itemId])) {
            return;
        }

        $qty = (int) $quantity;
        if ($qty <= 0) {
            unset($this->edit_cart[$itemId]);
            return;
        }

        $this->edit_cart[$itemId]['quantity'] = $qty;
    }

    public function setEditItemCostPrice(int $itemId, mixed $costPrice): void
    {
        $this->resetErrorBag('edit_cart');

        if (! isset($this->edit_cart[$itemId])) {
            return;
        }

        $v = (float) $costPrice;
        if ($v < 0) {
            $v = 0;
        }

        $this->edit_cart[$itemId]['cost_price'] = number_format($v, 2, '.', '');
    }

    public function removeEditItem(int $itemId): void
    {
        unset($this->edit_cart[$itemId]);
    }

    public function saveEdit(): void
    {
        $this->resetErrorBag();

        if (! auth()->user()?->can('stock_in.edit')) {
            $this->dispatch('banner-message', message: 'You do not have permission to edit stock in records.', style: 'danger');
            return;
        }

        if ($this->editing_receipt_id <= 0) {
            return;
        }

        $items = array_values($this->edit_cart);
        if (count($items) === 0) {
            $this->addError('edit_cart', 'No items in receipt.');
            return;
        }

        if (! $this->edit_reason || trim($this->edit_reason) === '' || mb_strlen(trim($this->edit_reason)) < 5) {
            $this->addError('edit_reason', 'A reason for this edit is required (minimum 5 characters).');
            return;
        }

        try {
            DB::transaction(function () use ($items) {
                $receipt = StockInReceipt::query()
                    ->whereKey($this->editing_receipt_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($receipt->voided_at) {
                    return;
                }

                if (! $this->isSuperAdmin) {
                    abort_unless((int) (auth()->user()?->branch_id ?? 0) === (int) $receipt->branch_id, 403);
                }

                $receipt->load(['items']);

                // Reverse old items stock
                foreach ($receipt->items as $item) {
                    $stock = ProductStock::query()
                        ->where('branch_id', (int) $receipt->branch_id)
                        ->where('product_id', (int) $item->product_id)
                        ->first();

                    if ($stock) {
                        $beforeStock = (int) $stock->current_stock;
                        $stock->current_stock = max(0, $beforeStock - (int) $item->quantity);
                        $stock->save();

                        StockMovement::query()->create([
                            'branch_id' => (int) $receipt->branch_id,
                            'product_id' => (int) $item->product_id,
                            'user_id' => auth()->id(),
                            'movement_type' => 'OUT',
                            'quantity' => (int) $item->quantity,
                            'before_stock' => $beforeStock,
                            'after_stock' => (int) $stock->current_stock,
                            'unit_cost' => $item->cost_price !== null ? (string) $item->cost_price : null,
                            'unit_price' => null,
                            'stock_in_receipt_id' => (int) $receipt->id,
                            'moved_at' => now(),
                            'notes' => 'STOCK IN EDIT REVERSE: ' . trim($this->edit_reason),
                        ]);
                    }
                }

                // Delete old items
                $receipt->items()->delete();

                // Add new items
                $totalQuantity = 0;
                $totalCost = 0.0;

                foreach ($items as $item) {
                    $product = Product::query()->find((int) $item['product_id']);
                    $costPrice = $item['cost_price'] !== null ? (float) $item['cost_price'] : 0.0;
                    $lineTotal = $costPrice * (int) $item['quantity'];

                    StockInItem::query()->create([
                        'stock_in_receipt_id' => (int) $receipt->id,
                        'product_id' => (int) $item['product_id'],
                        'quantity' => (int) $item['quantity'],
                        'remaining_quantity' => (int) $item['remaining_quantity'],
                        'cost_price' => $costPrice > 0 ? number_format($costPrice, 2, '.', '') : null,
                        'supplier_name' => $item['supplier_name'] ?? null,
                        'batch_ref_no' => $item['batch_ref_no'] ?? null,
                        'expiry_date' => $item['expiry_date'] ?? null,
                    ]);

                    // Update stock
                    $stock = ProductStock::query()->firstOrCreate(
                        ['branch_id' => (int) $receipt->branch_id, 'product_id' => (int) $item['product_id']],
                        ['current_stock' => 0, 'minimum_stock' => 0, 'cost_price' => null]
                    );

                    $stock = ProductStock::query()->whereKey($stock->id)->lockForUpdate()->firstOrFail();
                    $beforeStock = (int) $stock->current_stock;
                    $stock->current_stock = $beforeStock + (int) $item['quantity'];
                    $stock->save();

                    StockMovement::query()->create([
                        'branch_id' => (int) $receipt->branch_id,
                        'product_id' => (int) $item['product_id'],
                        'user_id' => auth()->id(),
                        'movement_type' => 'IN',
                        'quantity' => (int) $item['quantity'],
                        'before_stock' => $beforeStock,
                        'after_stock' => (int) $stock->current_stock,
                        'unit_cost' => $costPrice > 0 ? number_format($costPrice, 2, '.', '') : null,
                        'unit_price' => null,
                        'stock_in_receipt_id' => (int) $receipt->id,
                        'moved_at' => now(),
                        'notes' => 'STOCK IN EDIT: ' . trim($this->edit_reason),
                    ]);

                    $totalQuantity += (int) $item['quantity'];
                    $totalCost += $lineTotal;
                }

                $receipt->total_quantity = $totalQuantity;
                $receipt->total_cost = number_format($totalCost, 2, '.', '');
                $receipt->received_at = Carbon::parse($this->edit_received_at_date);
                $receipt->notes = $this->edit_notes;
                $receipt->save();

                ActivityLogger::log(
                    'stock_in.updated',
                    $receipt,
                    'Stock in receipt updated',
                    [
                        'branch_id' => (int) $receipt->branch_id,
                        'stock_in_receipt_id' => (int) $receipt->id,
                        'edit_reason' => trim($this->edit_reason),
                    ],
                    (int) $receipt->branch_id
                );
            });
        } catch (\Exception $e) {
            $this->addError('edit_cart', 'Failed to update receipt: ' . $e->getMessage());
            return;
        }

        $this->closeEditModal();
        session()->flash('status', 'Stock in receipt updated successfully.');
    }

    // ==================== VOID ====================

    public function openVoidModal(int $receiptId): void
    {
        $this->syncAuthContext();

        if (! auth()->user()?->can('stock_in.void')) {
            $this->dispatch('banner-message', message: 'You do not have permission to void stock in records.', style: 'danger');
            return;
        }

        $receipt = StockInReceipt::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->findOrFail($receiptId);

        if ($receipt->voided_at) {
            return;
        }

        $this->pending_void_receipt_id = (int) $receipt->id;
        $this->void_reason = null;
        $this->resetErrorBag();
        $this->show_void_modal = true;
    }

    public function closeVoidModal(): void
    {
        $this->show_void_modal = false;
        $this->pending_void_receipt_id = 0;
        $this->void_reason = null;
        $this->resetErrorBag();
    }

    public function confirmVoidReceipt(): void
    {
        $this->resetErrorBag();

        if (! auth()->user()?->can('stock_in.void')) {
            $this->dispatch('banner-message', message: 'You do not have permission to void stock in records.', style: 'danger');
            return;
        }

        if ($this->pending_void_receipt_id <= 0) {
            return;
        }

        $this->validate(['void_reason' => ['required', 'string', 'min:10', 'max:500']]);

        try {
            DB::transaction(function () {
                $receipt = StockInReceipt::query()
                    ->whereKey($this->pending_void_receipt_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($receipt->voided_at || $receipt->void_requested_at) {
                    return;
                }

                if (! $this->isSuperAdmin) {
                    abort_unless((int) (auth()->user()?->branch_id ?? 0) === (int) $receipt->branch_id, 403);
                }

                $receipt->load(['items']);

                // Check if any items have been sold
                foreach ($receipt->items as $item) {
                    if ((int) $item->remaining_quantity < (int) $item->quantity) {
                        $soldQty = (int) $item->quantity - (int) $item->remaining_quantity;
                        throw new \Exception("Cannot void: {$soldQty} units of {$item->product?->name} have already been sold.");
                    }
                }

                // Create pending stock adjustments for each item
                foreach ($receipt->items as $item) {
                    $stock = ProductStock::query()
                        ->where('branch_id', (int) $receipt->branch_id)
                        ->where('product_id', (int) $item->product_id)
                        ->first();

                    $currentStock = $stock ? (int) $stock->current_stock : 0;
                    $adjustmentQty = (int) $item->quantity;
                    $targetStock = max(0, $currentStock - $adjustmentQty);

                    StockAdjustment::query()->create([
                        'branch_id' => (int) $receipt->branch_id,
                        'product_id' => (int) $item->product_id,
                        'adjustment_type' => StockAdjustment::TYPE_STOCK_IN_VOID,
                        'current_stock' => $currentStock,
                        'adjustment_quantity' => -abs($adjustmentQty),
                        'target_stock' => $targetStock,
                        'status' => StockAdjustment::STATUS_PENDING,
                        'reason' => $this->void_reason,
                        'requested_by' => auth()->id(),
                        'source_type' => 'stock_in_receipt',
                        'source_id' => (int) $receipt->id,
                    ]);
                }

                // Mark receipt as void pending
                $receipt->void_requested_at = now();
                $receipt->void_requested_by = auth()->id();
                $receipt->void_reason = $this->void_reason;
                $receipt->save();

                ActivityLogger::log(
                    'stock_in.void_requested',
                    $receipt,
                    'Stock in void requested',
                    [
                        'branch_id' => (int) $receipt->branch_id,
                        'stock_in_receipt_id' => (int) $receipt->id,
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

        $query = StockInReceipt::query()
            ->with(['branch', 'user'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->status_filter === 'active', fn ($q) => $q->whereNull('voided_at')->whereNull('void_requested_at'))
            ->when($this->status_filter === 'void_pending', fn ($q) => $q->whereNotNull('void_requested_at')->whereNull('voided_at'))
            ->when($this->status_filter === 'voided', fn ($q) => $q->whereNotNull('voided_at'))
            ->when($this->date_from, fn ($q) => $q->whereDate('received_at', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('received_at', '<=', $this->date_to))
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('receipt_no', 'like', '%' . $this->search . '%')
                        ->orWhereHas('items', fn ($qi) => $qi->where('supplier_name', 'like', '%' . $this->search . '%'));
                });
            })
            ->orderBy('received_at', 'desc');

        $receipts = $query->paginate(20);

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.stock-in-records-index', [
            'receipts' => $receipts,
            'branches' => $branches,
        ]);
    }
}
