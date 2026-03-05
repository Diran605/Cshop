<?php

namespace App\Livewire\Clearance;

use App\Models\Branch;
use App\Models\ClearanceDiscountRule;
use App\Models\ClearanceItem;
use App\Models\Disposal;
use App\Models\Donation;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class ClearanceManager extends Component
{
    use WithPagination;

    public string $filter_status = 'all';
    public string $filter_action = 'pending';
    public string $search = '';

    public bool $isSuperAdmin = false;
    public int $filter_branch_id = 0;
    public int $userBranchId = 0;

    // Discount modal
    public bool $show_discount_modal = false;
    public ?int $discount_item_id = null;
    public float $discount_percentage = 0;
    public float $discount_custom_price = 0;
    public float $discount_original_price = 0;
    public float $discount_suggested_price = 0;

    // Donation modal
    public bool $show_donate_modal = false;
    public ?int $donate_item_id = null;
    public int $donate_quantity = 0;
    public int $donate_max_quantity = 0;
    public string $donate_organization = '';
    public string $donate_contact = '';
    public string $donate_address = '';
    public string $donate_notes = '';

    // Disposal modal
    public bool $show_dispose_modal = false;
    public ?int $dispose_item_id = null;
    public int $dispose_quantity = 0;
    public int $dispose_max_quantity = 0;
    public string $dispose_reason = '';
    public string $dispose_method = '';
    public string $dispose_notes = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->userBranchId = (int) ($user?->branch_id ?? 0);

        // Super admin starts with no branch filter (shows all)
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

    #[Computed]
    public function clearanceItems()
    {
        $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;

        return ClearanceItem::query()
            ->with(['product', 'discountRule', 'actionedBy'])
            ->when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->when($this->filter_status !== 'all', fn ($q) => $q->where('status', $this->filter_status))
            ->when($this->filter_action === 'pending', fn ($q) => $q->where('status', '!=', ClearanceItem::STATUS_ACTIONED))
            ->when($this->filter_action === 'actioned', fn ($q) => $q->where('status', ClearanceItem::STATUS_ACTIONED))
            ->when($this->search, function ($q) {
                $q->whereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$this->search}%"));
            })
            ->orderBy('days_to_expiry')
            ->paginate(15);
    }

    #[Computed]
    public function stats()
    {
        $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;

        return [
            'total_pending' => ClearanceItem::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
                ->where('status', '!=', ClearanceItem::STATUS_ACTIONED)
                ->count(),
            'total_value_at_risk' => ClearanceItem::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
                ->where('status', '!=', ClearanceItem::STATUS_ACTIONED)
                ->sum(DB::raw('original_price * quantity')),
            'by_status' => [
                'approaching' => ClearanceItem::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))->where('status', 'approaching')->count(),
                'urgent' => ClearanceItem::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))->where('status', 'urgent')->count(),
                'critical' => ClearanceItem::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))->where('status', 'critical')->count(),
                'expired' => ClearanceItem::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))->where('status', 'expired')->count(),
            ],
        ];
    }

    #[Computed]
    public function discountRules()
    {
        $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;

        return ClearanceDiscountRule::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->orWhereNull('branch_id')
            ->where('is_active', true)
            ->orderBy('days_to_expiry_min')
            ->get();
    }

    #[Computed]
    public function branches()
    {
        if (! $this->isSuperAdmin) {
            return collect();
        }

        return Branch::orderBy('name')->get(['id', 'name']);
    }

    public function openDiscountModal(int $itemId): void
    {
        $item = ClearanceItem::with(['product', 'discountRule'])->find($itemId);
        if (!$item) return;

        $this->discount_item_id = $itemId;
        $this->discount_original_price = $item->original_price;
        $this->discount_percentage = $item->suggested_discount_pct;
        $this->discount_suggested_price = $item->original_price * (1 - $item->suggested_discount_pct / 100);
        $this->discount_custom_price = $this->discount_suggested_price;
        $this->show_discount_modal = true;
    }

    public function updatedDiscountPercentage(): void
    {
        $this->discount_suggested_price = $this->discount_original_price * (1 - $this->discount_percentage / 100);
        $this->discount_custom_price = $this->discount_suggested_price;
    }

    public function applyDiscount(): void
    {
        $this->validate([
            'discount_custom_price' => 'required|numeric|min:0',
        ]);

        $item = ClearanceItem::find($this->discount_item_id);
        if (!$item) return;

        $item->applyDiscount($this->discount_percentage, $this->discount_custom_price);

        ActivityLogger::log(
            'clearance_discount_applied',
            $item,
            "Applied {$this->discount_percentage}% discount to {$item->product?->name}",
            ['discount_pct' => $this->discount_percentage, 'new_price' => $this->discount_custom_price]
        );

        $this->show_discount_modal = false;
        $this->reset(['discount_item_id', 'discount_percentage', 'discount_custom_price']);

        $this->dispatch('clearance-actioned');
        session()->flash('success', 'Discount applied successfully. Item will now sell at clearance price.');
    }

    public function openDonateModal(int $itemId): void
    {
        $item = ClearanceItem::with(['product'])->find($itemId);
        if (!$item) return;

        $this->donate_item_id = $itemId;
        $this->donate_max_quantity = $item->quantity;
        $this->donate_quantity = $item->quantity;
        $this->donate_organization = '';
        $this->donate_contact = '';
        $this->donate_address = '';
        $this->donate_notes = '';
        $this->show_donate_modal = true;
    }

    public function recordDonation(): void
    {
        $this->validate([
            'donate_quantity' => 'required|integer|min:1|max:' . $this->donate_max_quantity,
            'donate_organization' => 'required|string|min:2',
        ]);

        $item = ClearanceItem::find($this->donate_item_id);
        if (!$item) return;

        $donation = $item->recordDonation(
            $this->donate_quantity,
            $this->donate_organization,
            $this->donate_contact,
            $this->donate_address,
            $this->donate_notes
        );

        // Decrease stock
        $this->decreaseStock($item->stock_in_item_id, $this->donate_quantity);

        ActivityLogger::log(
            'clearance_donation_recorded',
            $donation,
            "Donated {$this->donate_quantity} units of {$item->product?->name} to {$this->donate_organization}",
            ['quantity' => $this->donate_quantity, 'organization' => $this->donate_organization, 'receipt_number' => $donation->receipt_number]
        );

        $this->show_donate_modal = false;
        $this->reset(['donate_item_id', 'donate_quantity', 'donate_organization', 'donate_contact', 'donate_address', 'donate_notes']);

        $this->dispatch('clearance-actioned');
        session()->flash('success', "Donation recorded. Receipt #: {$donation->receipt_number}");
    }

    public function openDisposeModal(int $itemId): void
    {
        $item = ClearanceItem::with(['product'])->find($itemId);
        if (!$item) return;

        $this->dispose_item_id = $itemId;
        $this->dispose_max_quantity = $item->quantity;
        $this->dispose_quantity = $item->quantity;
        $this->dispose_reason = '';
        $this->dispose_method = '';
        $this->dispose_notes = '';
        $this->show_dispose_modal = true;
    }

    public function recordDisposal(): void
    {
        $this->validate([
            'dispose_quantity' => 'required|integer|min:1|max:' . $this->dispose_max_quantity,
            'dispose_reason' => 'required|string',
        ]);

        $item = ClearanceItem::find($this->dispose_item_id);
        if (!$item) return;

        $disposal = $item->recordDisposal(
            $this->dispose_quantity,
            $this->dispose_reason,
            $this->dispose_method,
            $this->dispose_notes
        );

        // Decrease stock
        $this->decreaseStock($item->stock_in_item_id, $this->dispose_quantity);

        ActivityLogger::log(
            'clearance_disposal_recorded',
            $disposal,
            "Disposed {$this->dispose_quantity} units of {$item->product?->name}",
            ['quantity' => $this->dispose_quantity, 'reason' => $this->dispose_reason, 'method' => $this->dispose_method]
        );

        $this->show_dispose_modal = false;
        $this->reset(['dispose_item_id', 'dispose_quantity', 'dispose_reason', 'dispose_method', 'dispose_notes']);

        $this->dispatch('clearance-actioned');
        session()->flash('success', 'Disposal recorded. Stock has been adjusted.');
    }

    protected function decreaseStock(int $stockInItemId, int $quantity): void
    {
        DB::table('stock_in_items')
            ->where('id', $stockInItemId)
            ->decrement('remaining_quantity', $quantity);
    }

    public function getStatusBadgeClass(string $status): string
    {
        return match ($status) {
            'approaching' => 'bg-yellow-100 text-yellow-800',
            'urgent' => 'bg-orange-100 text-orange-800',
            'critical' => 'bg-red-100 text-red-800',
            'expired' => 'bg-gray-100 text-gray-800',
            'actioned' => 'bg-green-100 text-green-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    public function render()
    {
        return view('livewire.clearance.clearance-manager');
    }
}
