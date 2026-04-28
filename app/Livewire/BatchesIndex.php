<?php

namespace App\Livewire;

use App\Models\ClearanceItem;
use App\Models\Product;
use App\Models\StockClearanceAllocation;
use App\Models\StockInItem;
use App\Models\StockInReceipt;
use App\Models\StockMovement;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class BatchesIndex extends Component
{
    use WithPagination;

    public int $branch_id = 0;
    public string $search = '';
    public string $status_filter = 'all';
    public int $product_id = 0;

    public bool $isSuperAdmin = false;
    public ?int $userBranchId = 0;

    // Modal properties
    public bool $show_clearance_modal = false;
    public int $selected_stock_in_item_id = 0;
    public ?int $selected_remaining_qty = 0;
    public string $clearance_action = 'partial'; // 'partial' or 'entire'
    public int $clearance_partial_qty = 0;
    public ?string $clearance_reason = null;

    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        return [];
    }

    public function mount(): void
    {
        $this->syncAuthContext();
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->userBranchId = (int) ($user?->branch_id ?? 0);

        if (!$this->isSuperAdmin) {
            $this->branch_id = $this->userBranchId;
        }
    }

    public function openClearanceModal(int $stockInItemId, int $remainingQty): void
    {
        $this->show_clearance_modal = true;
        $this->selected_stock_in_item_id = $stockInItemId;
        $this->selected_remaining_qty = $remainingQty;
        $this->clearance_action = 'partial';
        $this->clearance_partial_qty = 0;
        $this->clearance_reason = null;
    }

    public function closeClearanceModal(): void
    {
        $this->show_clearance_modal = false;
        $this->selected_stock_in_item_id = 0;
        $this->selected_remaining_qty = 0;
        $this->clearance_action = 'partial';
        $this->clearance_partial_qty = 0;
        $this->clearance_reason = null;
    }

    public function sendToClearance(): void
    {
        if (! auth()->user()?->can('clearance.send')) {
            $this->addError('clearance', 'You do not have permission to send items to clearance.');
            return;
        }

        if ($this->selected_stock_in_item_id <= 0) {
            $this->addError('clearance', 'No item selected.');
            return;
        }

        if (!$this->clearance_reason || trim($this->clearance_reason) === '') {
            $this->addError('clearance', 'Please provide a reason for sending to clearance.');
            return;
        }

        $stockInItem = StockInItem::with(['receipt', 'product'])->find($this->selected_stock_in_item_id);
        if (!$stockInItem) {
            $this->addError('clearance', 'Stock item not found.');
            return;
        }

        if (!$stockInItem->receipt) {
            $this->addError('clearance', 'Stock receipt not found for this item.');
            return;
        }

        if (!$stockInItem->product) {
            $this->addError('clearance', 'Product not found for this item.');
            return;
        }

        // Re-check remaining quantity from DB to prevent race conditions
        $currentRemaining = (int) $stockInItem->remaining_quantity;
        if ($currentRemaining <= 0) {
            $this->addError('clearance', 'This batch has no remaining stock.');
            return;
        }

        // Determine allocation quantity
        $allocatedQty = 0;
        if ($this->clearance_action === 'entire') {
            $allocatedQty = $currentRemaining;
        } else {
            // Partial allocation
            if ($this->clearance_partial_qty <= 0 || $this->clearance_partial_qty > $currentRemaining) {
                $this->addError('clearance', 'Invalid quantity. Must be between 1 and ' . $currentRemaining . '.');
                return;
            }
            $allocatedQty = $this->clearance_partial_qty;
        }

        DB::beginTransaction();
        try {
            // Re-lock the item to prevent concurrency issues
            $stockInItem = StockInItem::query()
                ->whereKey($this->selected_stock_in_item_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Double-check remaining quantity under lock
            if ((int) $stockInItem->remaining_quantity < $allocatedQty) {
                $this->addError('clearance', 'Not enough remaining stock. Only ' . $stockInItem->remaining_quantity . ' available.');
                DB::rollBack();
                return;
            }

            $branchId = (int) ($stockInItem->receipt?->branch_id ?? 0);
            $productName = $stockInItem->product?->name ?? 'Unknown';
            $sellingPrice = (float) ($stockInItem->product?->selling_price ?? 0);

            // Create StockClearanceAllocation
            $allocation = StockClearanceAllocation::create([
                'stock_in_item_id' => $this->selected_stock_in_item_id,
                'allocated_quantity' => $allocatedQty,
                'reason' => $this->clearance_reason,
                'created_by' => auth()->id(),
            ]);

            // Create ClearanceItem
            $expiryDate = $stockInItem->expiry_date ?? Carbon::today()->addDays(30)->toDateString();
            $daysToExpiry = $stockInItem->expiry_date
                ? (int) Carbon::today()->diffInDays(Carbon::parse($stockInItem->expiry_date), false)
                : 30;

            $clearanceItem = ClearanceItem::create([
                'branch_id' => $branchId,
                'stock_in_item_id' => $this->selected_stock_in_item_id,
                'product_id' => $stockInItem->product_id,
                'expiry_date' => $expiryDate,
                'days_to_expiry' => $daysToExpiry,
                'status' => 'approaching',
                'quantity' => $allocatedQty,
                'original_price' => $sellingPrice,
                'suggested_discount_pct' => 0,
                'approval_status' => 'manual',
                'suggested_at' => now(),
            ]);

            // Deduct remaining quantity from the batch
            $beforeRemaining = (int) $stockInItem->remaining_quantity;
            $stockInItem->remaining_quantity = $beforeRemaining - $allocatedQty;
            $stockInItem->save();

            // Create StockMovement
            StockMovement::create([
                'branch_id' => $branchId,
                'product_id' => $stockInItem->product_id,
                'user_id' => auth()->id(),
                'movement_type' => 'clearance_allocation',
                'quantity' => $allocatedQty,
                'before_stock' => $beforeRemaining,
                'after_stock' => $beforeRemaining - $allocatedQty,
                'unit_cost' => $stockInItem->cost_price,
                'stock_in_receipt_id' => $stockInItem->stock_in_receipt_id,
                'moved_at' => now(),
                'notes' => "Allocated to clearance: {$this->clearance_reason}",
            ]);

            // Update ProductStock to reflect the reduction
            $productStock = \App\Models\ProductStock::query()
                ->where('branch_id', $branchId)
                ->where('product_id', $stockInItem->product_id)
                ->first();

            if ($productStock) {
                $beforeStock = (int) $productStock->current_stock;
                $productStock->current_stock = max(0, $beforeStock - $allocatedQty);
                $productStock->save();
            }

            // Log activity
            ActivityLogger::log(
                'clearance.manual_send',
                $clearanceItem,
                "Manually sent {$productName} ({$allocatedQty} units) to clearance",
                [
                    'stock_in_item_id' => $this->selected_stock_in_item_id,
                    'allocated_quantity' => $allocatedQty,
                    'reason' => $this->clearance_reason,
                ],
                $branchId
            );

            DB::commit();

            session()->flash('status', "Successfully sent {$allocatedQty} units to clearance.");
            $this->closeClearanceModal();
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('clearance', 'Error: ' . $e->getMessage());
        }
    }


    public function render()
    {
        $this->syncAuthContext();

        $branchId = $this->isSuperAdmin && $this->branch_id > 0 ? $this->branch_id : $this->userBranchId;

        $batches = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->join('products', 'products.id', '=', 'stock_in_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->when($branchId > 0, fn($q) => $q->where('stock_in_receipts.branch_id', $branchId))
            ->when($this->product_id > 0, fn($q) => $q->where('stock_in_items.product_id', $this->product_id))
            ->when($this->search, function ($q) {
                $term = '%' . $this->search . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('products.name', 'like', $term)
                        ->orWhere('stock_in_items.batch_ref_no', 'like', $term)
                        ->orWhere('stock_in_receipts.receipt_no', 'like', $term);
                });
            })
            ->when($this->status_filter === 'active', fn($q) => $q->where('stock_in_items.remaining_quantity', '>', 0)
                ->where(function ($qq) {
                    $qq->whereNull('stock_in_items.expiry_date')
                        ->orWhere('stock_in_items.expiry_date', '>=', now()->toDateString());
                })
                ->whereNull('stock_in_receipts.voided_at'))
            ->when($this->status_filter === 'expiring', fn($q) => $q->where('stock_in_items.remaining_quantity', '>', 0)
                ->where('stock_in_items.expiry_date', '<', now()->addDays(30)->toDateString())
                ->where('stock_in_items.expiry_date', '>=', now()->toDateString())
                ->whereNull('stock_in_receipts.voided_at'))
            ->when($this->status_filter === 'expired', fn($q) => $q->where('stock_in_items.remaining_quantity', '>', 0)
                ->where('stock_in_items.expiry_date', '<', now()->toDateString())
                ->whereNull('stock_in_receipts.voided_at'))
            ->when($this->status_filter === 'voided', fn($q) => $q->whereNotNull('stock_in_receipts.voided_at'))
            ->select([
                'stock_in_items.id',
                'stock_in_items.product_id',
                'stock_in_items.batch_ref_no',
                'stock_in_items.quantity as original_quantity',
                'stock_in_items.remaining_quantity',
                'stock_in_items.cost_price',
                'stock_in_items.expiry_date',
                'stock_in_items.entry_mode',
                'stock_in_items.created_at',
                'stock_in_receipts.id as receipt_id',
                'stock_in_receipts.receipt_no',
                'stock_in_receipts.received_at',
                'stock_in_receipts.voided_at',
                'products.name as product_name',
                'categories.name as category_name',
            ])
            ->orderByDesc('stock_in_items.created_at')
            ->paginate(20);

        $products = Product::query()
            ->where('status', Product::STATUS_ACTIVE)
            ->when($branchId > 0, fn($q) => $q->where('branch_id', $branchId))
            ->orderBy('name')
            ->get(['id', 'name']);

        $branches = [];
        if ($this->isSuperAdmin) {
            $branches = \App\Models\Branch::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        return view('livewire.batches-index', [
            'batches' => $batches,
            'products' => $products,
            'branches' => $branches,
        ]);
    }

    public function getBatchStatus(StockInItem $batch): string
    {
        if ($batch->receipt && $batch->receipt->voided_at) {
            return 'voided';
        }

        if ($batch->remaining_quantity <= 0) {
            return 'depleted';
        }

        if (!$batch->expiry_date) {
            return 'active';
        }

        $daysToExpiry = now()->diffInDays($batch->expiry_date, false);

        if ($daysToExpiry < 0) {
            return 'expired';
        }

        if ($daysToExpiry <= 30) {
            return 'expiring';
        }

        return 'active';
    }
}
