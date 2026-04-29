<?php

namespace App\Livewire\Clearance;

use App\Models\Branch;
use App\Models\ClearanceAction;
use App\Models\ClearanceItem;
use App\Models\Disposal;
use App\Models\Donation;
use App\Support\ActivityLogger;
use Livewire\Component;
use Livewire\WithPagination;

class ClearanceRecords extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filter_action_type = 'all';
    public string $filter_status = 'all';
    public int $filter_branch_id = 0;

    public bool $isSuperAdmin = false;
    public int $userBranchId = 0;

    // View modal
    public bool $show_view_modal = false;
    public ?int $view_item_id = null;
    public $viewItem = null;

    // Edit modal
    public bool $show_edit_modal = false;
    public ?int $edit_item_id = null;
    public int $edit_quantity = 0;
    public float $edit_original_price = 0;
    public float $edit_clearance_price = 0;
    public ?string $edit_notes = null;

    // Reversal modal
    public bool $show_reversal_modal = false;
    public ?int $reversal_item_id = null;
    public string $reversal_reason = '';
    public bool $reversal_restore_to_stock = true;

    // Disposal modal
    public bool $show_dispose_modal = false;
    public ?int $dispose_item_id = null;
    public int $dispose_quantity = 0;
    public int $dispose_max_quantity = 0;
    public string $dispose_reason = '';
    public string $dispose_method = '';
    public string $dispose_notes = '';

    protected $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->userBranchId = (int) ($user->branch_id ?? 0);

        if ($this->isSuperAdmin) {
            $this->filter_branch_id = 0;
        } else {
            $this->filter_branch_id = $this->userBranchId;
        }
    }

    public function updatedFilterBranchId(): void
    {
        $this->resetPage();
    }

    public function updatedFilterActionType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function openViewModal(int $itemId): void
    {
        if (! auth()->user()?->can('clearance.records.view')) {
            session()->flash('error', 'You do not have permission to view clearance records.');
            return;
        }

        $this->view_item_id = $itemId;
        $this->viewItem = ClearanceItem::with(['product', 'branch', 'discountRule', 'actionedBy', 'actions'])
            ->findOrFail($itemId);
        $this->show_view_modal = true;
    }

    public function closeViewModal(): void
    {
        $this->show_view_modal = false;
        $this->view_item_id = null;
        $this->viewItem = null;
    }

    public function openEditModal(int $itemId): void
    {
        if (! auth()->user()?->can('clearance.records.edit')) {
            session()->flash('error', 'You do not have permission to edit clearance records.');
            return;
        }

        $item = ClearanceItem::findOrFail($itemId);

        $this->edit_item_id = $itemId;
        $this->edit_quantity = $item->quantity;
        $this->edit_original_price = (float) $item->original_price;
        $this->edit_clearance_price = (float) ($item->clearance_price ?? 0);
        $this->edit_notes = $item->notes;

        $this->show_edit_modal = true;
    }

    public function saveEdit(): void
    {
        $this->validate([
            'edit_quantity' => 'required|integer|min:1',
            'edit_original_price' => 'required|numeric|min:0',
            'edit_clearance_price' => 'nullable|numeric|min:0',
            'edit_notes' => 'nullable|string|max:500',
        ]);

        $item = ClearanceItem::findOrFail($this->edit_item_id);

        $item->update([
            'quantity' => $this->edit_quantity,
            'original_price' => $this->edit_original_price,
            'clearance_price' => $this->edit_clearance_price > 0 ? $this->edit_clearance_price : null,
            'notes' => $this->edit_notes,
        ]);

        ActivityLogger::log(
            'clearance_item.updated',
            $item,
            "Updated clearance item for {$item->product?->name}",
            ['quantity' => $this->edit_quantity, 'original_price' => $this->edit_original_price]
        );

        $this->show_edit_modal = false;
        $this->resetEditForm();
        session()->flash('status', 'Clearance record updated.');
    }

    public function closeEditModal(): void
    {
        $this->show_edit_modal = false;
        $this->resetEditForm();
    }

    protected function resetEditForm(): void
    {
        $this->edit_item_id = null;
        $this->edit_quantity = 0;
        $this->edit_original_price = 0;
        $this->edit_clearance_price = 0;
        $this->edit_notes = null;
    }

    public function delete(int $itemId): void
    {
        if (! auth()->user()?->can('clearance.records.delete')) {
            session()->flash('error', 'You do not have permission to delete clearance records.');
            return;
        }

        $item = ClearanceItem::findOrFail($itemId);
        $productName = $item->product?->name ?? 'Unknown';

        // Check if item has been actioned
        if ($item->status === ClearanceItem::STATUS_ACTIONED) {
            session()->flash('error', 'Cannot delete an actioned clearance item.');
            return;
        }

        $item->delete();

        ActivityLogger::log(
            'clearance_item.deleted',
            $item,
            "Deleted clearance item for {$productName}",
            ['product_name' => $productName]
        );

        session()->flash('status', 'Clearance record deleted.');
    }

    public function openReversalModal(int $itemId): void
    {
        if (! auth()->user()?->can('clearance.reverse')) {
            session()->flash('error', 'You do not have permission to reverse clearance actions.');
            return;
        }

        $item = ClearanceItem::with('stockInItem', 'product')->findOrFail($itemId);

        // Can only reverse actioned items
        if ($item->status !== ClearanceItem::STATUS_ACTIONED) {
            session()->flash('error', 'Only actioned clearance items can be reversed.');
            return;
        }

        $this->reversal_item_id = $itemId;
        $this->reversal_reason = '';
        $this->reversal_restore_to_stock = true;
        $this->show_reversal_modal = true;
    }

    public function closeReversalModal(): void
    {
        $this->show_reversal_modal = false;
        $this->reversal_item_id = null;
        $this->reversal_reason = '';
        $this->reversal_restore_to_stock = true;
    }

    public function openDisposeModal(int $itemId): void
    {
        if (! auth()->user()?->can('clearance.dispose')) {
            session()->flash('error', 'You do not have permission to dispose items.');
            return;
        }

        $item = ClearanceItem::with(['product'])->findOrFail($itemId);

        $this->dispose_item_id = $itemId;
        $this->dispose_max_quantity = $item->quantity;
        $this->dispose_quantity = $item->quantity;
        $this->dispose_reason = \App\Models\Disposal::REASON_EXPIRED;
        $this->dispose_method = \App\Models\Disposal::METHOD_TRASH;
        $this->dispose_notes = '';
        $this->show_dispose_modal = true;
    }

    public function recordDisposal(): void
    {
        $this->validate([
            'dispose_quantity' => 'required|integer|min:1|max:' . $this->dispose_max_quantity,
            'dispose_reason' => 'required|string',
            'dispose_method' => 'required|string',
        ]);

        if (! auth()->user()?->can('clearance.dispose')) {
            session()->flash('error', 'You do not have permission to dispose items.');
            return;
        }

        $item = ClearanceItem::with(['stockInItem', 'product'])->findOrFail($this->dispose_item_id);

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // 1. Record the disposal (this updates clearance item status to 'actioned' / 'dispose')
            $disposal = $item->recordDisposal(
                $this->dispose_quantity,
                $this->dispose_reason,
                $this->dispose_method,
                $this->dispose_notes
            );

            // 2. Reduce the physical stock in ProductStock
            $productStock = \App\Models\ProductStock::where('branch_id', $item->branch_id)
                ->where('product_id', $item->product_id)
                ->lockForUpdate()
                ->first();

            if ($productStock) {
                $beforeStock = $productStock->current_stock;
                $productStock->decrement('current_stock', $this->dispose_quantity);
                $afterStock = $productStock->current_stock;

                // 3. Create stock movement
                \App\Models\StockMovement::create([
                    'branch_id' => $item->branch_id,
                    'product_id' => $item->product_id,
                    'user_id' => auth()->id(),
                    'movement_type' => 'OUT',
                    'quantity' => $this->dispose_quantity,
                    'before_stock' => $beforeStock,
                    'after_stock' => $afterStock,
                    'unit_cost' => $item->original_price,
                    'moved_at' => now(),
                    'notes' => "DISPOSAL from Clearance: {$this->dispose_reason}",
                ]);
            }

            // 4. Reduce remaining quantity in the specific batch (StockInItem)
            if ($item->stockInItem) {
                $item->stockInItem->decrement('remaining_quantity', $this->dispose_quantity);
            }

            // 5. Update ClearanceItem quantity (if partially disposed, though here we usually dispose all remaining)
            $item->decrement('quantity', $this->dispose_quantity);
            if ($item->quantity <= 0) {
                $item->update(['status' => ClearanceItem::STATUS_ACTIONED, 'action_type' => ClearanceItem::ACTION_DISPOSE]);
            }

            ActivityLogger::log(
                'clearance.disposed',
                $item,
                "Recorded disposal for {$item->product?->name}",
                [
                    'quantity' => $this->dispose_quantity,
                    'reason' => $this->dispose_reason,
                ],
                $item->branch_id
            );

            \Illuminate\Support\Facades\DB::commit();

            $this->show_dispose_modal = false;
            session()->flash('status', "✓ Disposal recorded for {$item->product?->name}");
            $this->dispatch('clearance-updated');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            session()->flash('error', "Failed to record disposal: " . $e->getMessage());
        }
    }

    public function closeDisposeModal(): void
    {
        $this->show_dispose_modal = false;
        $this->dispose_item_id = null;
    }

    public function reverseAction(): void
    {
        $this->validate([
            'reversal_reason' => 'required|string|min:3|max:500',
        ]);

        if (! auth()->user()?->can('clearance.reverse')) {
            session()->flash('error', 'You do not have permission to reverse clearance actions.');
            return;
        }

        $item = ClearanceItem::with(['stockInItem', 'product'])->findOrFail($this->reversal_item_id);

        // Verify item is actioned
        if ($item->status !== ClearanceItem::STATUS_ACTIONED) {
            session()->flash('error', 'Cannot reverse non-actioned items.');
            $this->closeReversalModal();
            return;
        }

        try {
            \Illuminate\Support\Facades\DB::beginTransaction();

            // If restoring to stock, create stock movement
            if ($this->reversal_restore_to_stock && $item->stockInItem) {
                // Update ProductStock to reflect the returned inventory
                $productStock = \App\Models\ProductStock::firstOrCreate(
                    ['branch_id' => $item->branch_id, 'product_id' => $item->product_id],
                    ['current_stock' => 0]
                );
                
                $beforeStock = $productStock->current_stock;
                $productStock->increment('current_stock', $item->quantity);
                $afterStock = $productStock->current_stock;

                \App\Models\StockMovement::create([
                    'branch_id' => $item->branch_id,
                    'product_id' => $item->product_id,
                    'stock_in_receipt_id' => $item->stockInItem->stock_in_receipt_id ?? null,
                    'user_id' => auth()->id(),
                    'movement_type' => 'clearance_reversal',
                    'quantity' => $item->quantity,
                    'before_stock' => $beforeStock,
                    'after_stock' => $afterStock,
                    'unit_cost' => $item->original_price,
                    'moved_at' => now(),
                    'notes' => "Reversal: {$this->reversal_reason}",
                ]);

                // Restore quantity to stock batch
                $item->stockInItem->increment('remaining_quantity', $item->quantity);
            }

            // Mark existing active actions as reversed (Approach B)
            ClearanceAction::where('clearance_item_id', $item->id)
                ->where('status', ClearanceAction::STATUS_ACTIVE)
                ->update([
                    'status' => ClearanceAction::STATUS_REVERSED,
                    'reversal_reason' => $this->reversal_reason,
                    'reversed_at' => now(),
                    'reversed_by' => auth()->id(),
                ]);

            // Fully reset the clearance item back to a pending state
            $daysToExpiry = $item->calculateDaysToExpiry();
            $newStatus = \App\Models\ClearanceDiscountRule::determineStatus($daysToExpiry);

            $item->update([
                'status' => $newStatus,
                'action_type' => null,
                'actioned_at' => null,
                'actioned_by' => null,
                'clearance_price' => null,
                'approval_status' => 'reversed',
                'approval_notes' => "Reversal by " . auth()->user()->name . ": {$this->reversal_reason}",
                'days_to_expiry' => $daysToExpiry,
            ]);

            ActivityLogger::log(
                'clearance.reversed',
                $item,
                "Reversed clearance action for {$item->product?->name}",
                [
                    'action_type' => $item->action_type,
                    'quantity' => $item->quantity,
                    'reason' => $this->reversal_reason,
                    'restored_to_stock' => $this->reversal_restore_to_stock,
                ],
                $item->branch_id
            );

            \Illuminate\Support\Facades\DB::commit();

            $this->closeReversalModal();
            session()->flash('status', "✓ Clearance action reversed for {$item->product?->name}");
            $this->dispatch('clearance-updated');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            session()->flash('error', "Failed to reverse clearance action: {$e->getMessage()}");
        }
    }

    public function getBranchesProperty()
    {
        if (!$this->isSuperAdmin) {
            return collect();
        }

        return Branch::orderBy('name')->get(['id', 'name']);
    }

    public function getRecordsProperty()
    {
        $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;

        return ClearanceItem::query()
            ->with(['product', 'branch', 'discountRule', 'actionedBy'])
            ->when($branchId > 0, fn($q) => $q->where('branch_id', $branchId))
            ->when($this->filter_action_type !== 'all', fn($q) => $q->where('action_type', $this->filter_action_type))
            ->when($this->filter_status !== 'all', fn($q) => $q->where('status', $this->filter_status))
            ->when($this->search, function ($q) {
                $q->whereHas('product', fn($pq) => $pq->where('name', 'like', "%{$this->search}%"));
            })
            ->orderByDesc('id')
            ->paginate(20);
    }

    public function getStatsProperty()
    {
        $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;

        return [
            'total' => ClearanceItem::when($branchId > 0, fn($q) => $q->where('branch_id', $branchId))->count(),
            'pending' => ClearanceItem::when($branchId > 0, fn($q) => $q->where('branch_id', $branchId))
                ->where('status', '!=', ClearanceItem::STATUS_ACTIONED)->count(),
            'actioned' => ClearanceItem::when($branchId > 0, fn($q) => $q->where('branch_id', $branchId))
                ->where('status', ClearanceItem::STATUS_ACTIONED)->count(),
        ];
    }

    public function render()
    {
        return view('livewire.clearance.clearance-records', [
            'records' => $this->records,
            'stats' => $this->stats,
            'branches' => $this->branches,
        ]);
    }
}
