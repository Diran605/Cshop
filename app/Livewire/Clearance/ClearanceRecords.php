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
            ->when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->when($this->filter_action_type !== 'all', fn ($q) => $q->where('action_type', $this->filter_action_type))
            ->when($this->filter_status !== 'all', fn ($q) => $q->where('status', $this->filter_status))
            ->when($this->search, function ($q) {
                $q->whereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$this->search}%"));
            })
            ->orderByDesc('id')
            ->paginate(20);
    }

    public function getStatsProperty()
    {
        $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;

        return [
            'total' => ClearanceItem::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))->count(),
            'pending' => ClearanceItem::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
                ->where('status', '!=', ClearanceItem::STATUS_ACTIONED)->count(),
            'actioned' => ClearanceItem::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
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
