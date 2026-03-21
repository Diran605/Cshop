<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesItem;
use App\Models\SalesReceipt;
use App\Models\StockAdjustment;
use App\Models\StockInItem;
use App\Models\StockInReceipt;
use App\Models\StockMovement;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StockAdjustmentsIndex extends Component
{
    use WithPagination;

    public string $mode = 'pending';
    public int $branch_id = 0;
    public string $search = '';
    public string $status_filter = 'all';
    public string $type_filter = 'all';
    public string $date_from = '';
    public string $date_to = '';

    public bool $isSuperAdmin = false;
    public int $auth_user_id = 0;

    // View modal
    public bool $show_view_modal = false;
    public ?StockAdjustment $viewing_adjustment = null;

    // Approval modal
    public bool $show_approve_modal = false;
    public int $pending_approve_id = 0;

    // Rejection modal
    public bool $show_reject_modal = false;
    public int $pending_reject_id = 0;
    public ?string $rejection_reason = null;

    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        return [
            'rejection_reason' => ['required', 'string', 'min:3', 'max:500'],
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

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode;
        $this->resetPage();
    }

    // ==================== VIEW ====================

    public function viewAdjustment(int $id): void
    {
        $this->syncAuthContext();

        $adjustment = StockAdjustment::query()
            ->with(['branch', 'product', 'requester', 'reviewer'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->findOrFail($id);

        $this->viewing_adjustment = $adjustment;
        $this->show_view_modal = true;
    }

    public function closeViewModal(): void
    {
        $this->show_view_modal = false;
        $this->viewing_adjustment = null;
    }

    // ==================== APPROVE ====================

    public function openApproveModal(int $id): void
    {
        $this->syncAuthContext();

        if (! auth()->user()?->can('stock_adjustments.approve')) {
            $this->dispatch('banner-message', message: 'You do not have permission to approve adjustments.', style: 'danger');
            return;
        }

        $adjustment = StockAdjustment::query()
            ->with(['product', 'branch'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->findOrFail($id);

        if (! $adjustment->isPending()) {
            return;
        }

        $this->pending_approve_id = (int) $adjustment->id;
        $this->viewing_adjustment = $adjustment;
        $this->resetErrorBag();
        $this->show_approve_modal = true;
    }

    public function closeApproveModal(): void
    {
        $this->show_approve_modal = false;
        $this->pending_approve_id = 0;
        $this->resetErrorBag();
    }

    public function confirmApprove(): void
    {
        $this->syncAuthContext();

        if (! auth()->user()?->can('stock_adjustments.approve')) {
            $this->dispatch('banner-message', message: 'You do not have permission to approve adjustments.', style: 'danger');
            return;
        }

        if ($this->pending_approve_id <= 0) {
            return;
        }

        try {
            DB::transaction(function () {
                $adjustment = StockAdjustment::query()
                    ->whereKey($this->pending_approve_id)
                    ->with(['product'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $adjustment->isPending()) {
                    return;
                }

                if (! $this->isSuperAdmin) {
                    abort_unless((int) (auth()->user()?->branch_id ?? 0) === (int) $adjustment->branch_id, 403);
                }

                // Get current stock
                $stock = ProductStock::query()
                    ->where('branch_id', (int) $adjustment->branch_id)
                    ->where('product_id', (int) $adjustment->product_id)
                    ->lockForUpdate()
                    ->first();

                $beforeStock = $stock ? (int) $stock->current_stock : 0;
                $afterStock = (int) $adjustment->target_stock;

                // Update stock
                if ($stock) {
                    $stock->current_stock = $afterStock;
                    $stock->save();
                } else {
                    $stock = ProductStock::query()->create([
                        'branch_id' => (int) $adjustment->branch_id,
                        'product_id' => (int) $adjustment->product_id,
                        'current_stock' => $afterStock,
                        'minimum_stock' => 0,
                        'cost_price' => null,
                    ]);
                }

                // Create stock movement
                $movementType = $afterStock > $beforeStock ? 'IN' : 'OUT';
                $quantity = abs($afterStock - $beforeStock);

                StockMovement::query()->create([
                    'branch_id' => (int) $adjustment->branch_id,
                    'product_id' => (int) $adjustment->product_id,
                    'user_id' => auth()->id(),
                    'movement_type' => $movementType,
                    'quantity' => $quantity,
                    'before_stock' => $beforeStock,
                    'after_stock' => $afterStock,
                    'unit_cost' => null,
                    'unit_price' => null,
                    'stock_in_receipt_id' => null,
                    'sales_receipt_id' => null,
                    'moved_at' => now(),
                    'notes' => 'STOCK ADJUSTMENT: ' . $adjustment->adjustment_type . ' - ' . $adjustment->reason,
                ]);

                // Update adjustment status
                $adjustment->status = StockAdjustment::STATUS_APPROVED;
                $adjustment->reviewed_by = auth()->id();
                $adjustment->reviewed_at = now();
                $adjustment->save();

                // If this is a void_product adjustment, update product status
                if ($adjustment->adjustment_type === StockAdjustment::TYPE_VOID_PRODUCT) {
                    $product = Product::query()->find((int) $adjustment->product_id);
                    if ($product) {
                        $product->status = Product::STATUS_VOIDED;
                        $product->save();
                    }
                }

                // If this is a stock_in_void adjustment, check if all adjustments for this receipt are approved
                if ($adjustment->adjustment_type === StockAdjustment::TYPE_STOCK_IN_VOID && $adjustment->source_type === 'stock_in_receipt') {
                    $receiptId = (int) $adjustment->source_id;
                    $allApproved = ! StockAdjustment::query()
                        ->where('source_type', 'stock_in_receipt')
                        ->where('source_id', $receiptId)
                        ->where('status', '!=', StockAdjustment::STATUS_APPROVED)
                        ->exists();

                    if ($allApproved) {
                        // All adjustments approved - complete the void
                        $receipt = StockInReceipt::query()->whereKey($receiptId)->first();
                        if ($receipt && $receipt->void_requested_at && ! $receipt->voided_at) {
                            // Set remaining_quantity to 0 for all items
                            StockInItem::query()
                                ->where('stock_in_receipt_id', $receiptId)
                                ->update(['remaining_quantity' => 0]);

                            // Mark receipt as voided
                            $receipt->voided_at = now();
                            $receipt->voided_by = auth()->id();
                            $receipt->void_reviewed_by = auth()->id();
                            $receipt->void_reviewed_at = now();
                            $receipt->save();

                            // Recalculate WAC for affected products
                            $items = StockInItem::query()->where('stock_in_receipt_id', $receiptId)->get();
                            foreach ($items as $item) {
                                $product = Product::query()->find($item->product_id);
                                if ($product) {
                                    $this->recalculateWac($item->product_id, (int) $receipt->branch_id);
                                }
                            }

                            ActivityLogger::log(
                                'stock_in.voided',
                                $receipt,
                                'Stock in receipt voided after approval',
                                [
                                    'branch_id' => (int) $receipt->branch_id,
                                    'stock_in_receipt_id' => (int) $receipt->id,
                                    'void_reason' => $receipt->void_reason,
                                ],
                                (int) $receipt->branch_id
                            );
                        }
                    }
                }

                // If this is a sales_void adjustment, check if all adjustments for this receipt are approved
                if ($adjustment->adjustment_type === StockAdjustment::TYPE_SALES_VOID && $adjustment->source_type === 'sales_receipt') {
                    $receiptId = (int) $adjustment->source_id;
                    $allApproved = ! StockAdjustment::query()
                        ->where('source_type', 'sales_receipt')
                        ->where('source_id', $receiptId)
                        ->where('status', '!=', StockAdjustment::STATUS_APPROVED)
                        ->exists();

                    if ($allApproved) {
                        // All adjustments approved - complete the void
                        $receipt = SalesReceipt::query()->whereKey($receiptId)->first();
                        if ($receipt && $receipt->void_requested_at && ! $receipt->voided_at) {
                            // Restore stock_in_item remaining_quantities via sales_item_allocations
                            $salesItems = SalesItem::query()->where('sales_receipt_id', $receiptId)->get();
                            foreach ($salesItems as $item) {
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

                                // Delete allocations
                                DB::table('sales_item_allocations')
                                    ->where('sales_item_id', (int) $item->id)
                                    ->delete();
                            }

                            // Mark receipt as voided
                            $receipt->voided_at = now();
                            $receipt->voided_by = auth()->id();
                            $receipt->void_reviewed_by = auth()->id();
                            $receipt->void_reviewed_at = now();
                            $receipt->save();

                            ActivityLogger::log(
                                'sale.voided',
                                $receipt,
                                'Sale voided after approval',
                                [
                                    'branch_id' => (int) $receipt->branch_id,
                                    'sales_receipt_id' => (int) $receipt->id,
                                    'customer_name' => $receipt->customer_name,
                                    'void_reason' => $receipt->void_reason,
                                ],
                                (int) $receipt->branch_id
                            );
                        }
                    }
                }

                ActivityLogger::log(
                    'stock_adjustment.approved',
                    $adjustment,
                    'Stock adjustment approved',
                    [
                        'branch_id' => (int) $adjustment->branch_id,
                        'adjustment_id' => (int) $adjustment->id,
                        'product_id' => (int) $adjustment->product_id,
                        'before_stock' => $beforeStock,
                        'after_stock' => $afterStock,
                    ],
                    (int) $adjustment->branch_id
                );
            });
        } catch (\Exception $e) {
            $this->addError('approve', 'Failed to approve adjustment: ' . $e->getMessage());
            return;
        }

        $this->closeApproveModal();
        session()->flash('status', 'Adjustment approved successfully.');
    }

    // ==================== REJECT ====================

    public function openRejectModal(int $id): void
    {
        $this->syncAuthContext();

        if (! auth()->user()?->can('stock_adjustments.reject')) {
            $this->dispatch('banner-message', message: 'You do not have permission to reject adjustments.', style: 'danger');
            return;
        }

        $adjustment = StockAdjustment::query()
            ->with(['product', 'branch'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->findOrFail($id);

        if (! $adjustment->isPending()) {
            return;
        }

        $this->pending_reject_id = (int) $adjustment->id;
        $this->viewing_adjustment = $adjustment;
        $this->rejection_reason = null;
        $this->resetErrorBag();
        $this->show_reject_modal = true;
    }

    public function closeRejectModal(): void
    {
        $this->show_reject_modal = false;
        $this->pending_reject_id = 0;
        $this->rejection_reason = null;
        $this->resetErrorBag();
    }

    public function confirmReject(): void
    {
        $this->syncAuthContext();

        if (! auth()->user()?->can('stock_adjustments.reject')) {
            $this->dispatch('banner-message', message: 'You do not have permission to reject adjustments.', style: 'danger');
            return;
        }

        if ($this->pending_reject_id <= 0) {
            return;
        }

        $this->validate();

        try {
            DB::transaction(function () {
                $adjustment = StockAdjustment::query()
                    ->whereKey($this->pending_reject_id)
                    ->with(['product'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $adjustment->isPending()) {
                    return;
                }

                if (! $this->isSuperAdmin) {
                    abort_unless((int) (auth()->user()?->branch_id ?? 0) === (int) $adjustment->branch_id, 403);
                }

                // Update adjustment status
                $adjustment->status = StockAdjustment::STATUS_REJECTED;
                $adjustment->reviewed_by = auth()->id();
                $adjustment->reviewed_at = now();
                $adjustment->rejection_reason = $this->rejection_reason;
                $adjustment->save();

                // If this is a void_product adjustment, revert product status
                if ($adjustment->adjustment_type === StockAdjustment::TYPE_VOID_PRODUCT) {
                    $product = Product::query()->find((int) $adjustment->product_id);
                    if ($product && $product->status === Product::STATUS_VOID_PENDING) {
                        $product->status = Product::STATUS_ACTIVE;
                        $product->void_requested_at = null;
                        $product->void_requested_by = null;
                        $product->void_reason = null;
                        $product->save();
                    }
                }

                // If this is a stock_in_void adjustment, check if we need to reset the receipt
                if ($adjustment->adjustment_type === StockAdjustment::TYPE_STOCK_IN_VOID && $adjustment->source_type === 'stock_in_receipt') {
                    $receiptId = (int) $adjustment->source_id;

                    // Delete all pending adjustments for this receipt
                    StockAdjustment::query()
                        ->where('source_type', 'stock_in_receipt')
                        ->where('source_id', $receiptId)
                        ->where('status', StockAdjustment::STATUS_PENDING)
                        ->delete();

                    // Reset receipt to active
                    $receipt = StockInReceipt::query()->whereKey($receiptId)->first();
                    if ($receipt && $receipt->void_requested_at && ! $receipt->voided_at) {
                        $receipt->void_requested_at = null;
                        $receipt->void_requested_by = null;
                        $receipt->void_reason = null;
                        $receipt->save();

                        ActivityLogger::log(
                            'stock_in.void_rejected',
                            $receipt,
                            'Stock in void request rejected',
                            [
                                'branch_id' => (int) $receipt->branch_id,
                                'stock_in_receipt_id' => (int) $receipt->id,
                                'rejection_reason' => $this->rejection_reason,
                            ],
                            (int) $receipt->branch_id
                        );
                    }
                }

                // If this is a sales_void adjustment, check if we need to reset the receipt
                if ($adjustment->adjustment_type === StockAdjustment::TYPE_SALES_VOID && $adjustment->source_type === 'sales_receipt') {
                    $receiptId = (int) $adjustment->source_id;

                    // Delete all pending adjustments for this receipt
                    StockAdjustment::query()
                        ->where('source_type', 'sales_receipt')
                        ->where('source_id', $receiptId)
                        ->where('status', StockAdjustment::STATUS_PENDING)
                        ->delete();

                    // Reset receipt to active
                    $receipt = SalesReceipt::query()->whereKey($receiptId)->first();
                    if ($receipt && $receipt->void_requested_at && ! $receipt->voided_at) {
                        $receipt->void_requested_at = null;
                        $receipt->void_requested_by = null;
                        $receipt->void_reason = null;
                        $receipt->save();

                        ActivityLogger::log(
                            'sale.void_rejected',
                            $receipt,
                            'Sale void request rejected',
                            [
                                'branch_id' => (int) $receipt->branch_id,
                                'sales_receipt_id' => (int) $receipt->id,
                                'customer_name' => $receipt->customer_name,
                                'rejection_reason' => $this->rejection_reason,
                            ],
                            (int) $receipt->branch_id
                        );
                    }
                }

                ActivityLogger::log(
                    'stock_adjustment.rejected',
                    $adjustment,
                    'Stock adjustment rejected',
                    [
                        'branch_id' => (int) $adjustment->branch_id,
                        'adjustment_id' => (int) $adjustment->id,
                        'product_id' => (int) $adjustment->product_id,
                        'rejection_reason' => $this->rejection_reason,
                    ],
                    (int) $adjustment->branch_id
                );
            });
        } catch (\Exception $e) {
            $this->addError('rejection_reason', 'Failed to reject adjustment: ' . $e->getMessage());
            return;
        }

        $this->closeRejectModal();
        session()->flash('status', 'Adjustment rejected.');
    }

    // ==================== RENDER ====================

    public function render()
    {
        $this->syncAuthContext();

        $query = StockAdjustment::query()
            ->with(['branch', 'product', 'requester', 'reviewer'])
            ->whereIn('adjustment_type', [
                StockAdjustment::TYPE_VOID_PRODUCT,
                StockAdjustment::TYPE_STOCK_IN_VOID,
                StockAdjustment::TYPE_SALES_VOID,
            ])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->status_filter !== 'all', fn ($q) => $q->where('status', $this->status_filter))
            ->when($this->type_filter !== 'all', fn ($q) => $q->where('adjustment_type', $this->type_filter))
            ->when($this->date_from, fn ($q) => $q->whereDate('created_at', '>=', $this->date_from))
            ->when($this->date_to, fn ($q) => $q->whereDate('created_at', '<=', $this->date_to))
            ->when($this->search, function ($q) {
                $q->whereHas('product', function ($pq) {
                    $pq->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->mode === 'pending', fn ($q) => $q->where('status', StockAdjustment::STATUS_PENDING))
            ->when($this->mode === 'history', fn ($q) => $q->whereIn('status', [StockAdjustment::STATUS_APPROVED, StockAdjustment::STATUS_REJECTED]))
            ->orderByDesc('created_at');

        $adjustments = $query->paginate(20);

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.stock-adjustments-index', [
            'adjustments' => $adjustments,
            'branches' => $branches,
        ]);
    }

    protected function recalculateWac(int $productId, int $branchId): void
    {
        $product = Product::query()->find($productId);
        if (!$product) {
            return;
        }

        // Get all non-voided stock-in items for this product and branch
        $stockItems = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->whereNull('stock_in_receipts.voided_at')
            ->where('stock_in_items.product_id', $productId)
            ->where('stock_in_receipts.branch_id', $branchId)
            ->where('stock_in_items.remaining_quantity', '>', 0)
            ->whereNotNull('stock_in_items.cost_price')
            ->select([
                'stock_in_items.remaining_quantity',
                'stock_in_items.cost_price'
            ])
            ->get();

        if ($stockItems->isEmpty()) {
            $product->weighted_average_cost = null;
            $product->save();
            return;
        }

        $totalQty = 0;
        $totalCost = 0;

        foreach ($stockItems as $item) {
            $qty = (int) $item->remaining_quantity;
            $cost = (float) $item->cost_price;
            $totalQty += $qty;
            $totalCost += ($qty * $cost);
        }

        if ($totalQty > 0) {
            $wac = $totalCost / $totalQty;
            $product->weighted_average_cost = number_format($wac, 2, '.', '');
            $product->save();
        }
    }
}
