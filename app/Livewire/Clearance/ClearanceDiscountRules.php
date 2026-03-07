<?php

namespace App\Livewire\Clearance;

use App\Models\Branch;
use App\Models\ClearanceDiscountRule;
use App\Support\ActivityLogger;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ClearanceDiscountRules extends Component
{
    public bool $show_modal = false;
    public ?int $editing_id = null;

    public bool $show_view_modal = false;
    public ?int $viewing_id = null;

    public bool $show_delete_modal = false;
    public ?int $deleting_id = null;
    public string $deleting_rule_info = '';

    public int $days_to_expiry_min = 0;
    public int $days_to_expiry_max = 0;
    public int $discount_percentage = 0;
    public string $status_label = '';
    public bool $is_active = true;

    public bool $isSuperAdmin = false;
    public int $filter_branch_id = 0;
    public int $userBranchId = 0;

    public function mount(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->userBranchId = (int) ($user?->branch_id ?? 0);

        if ($this->isSuperAdmin) {
            $this->filter_branch_id = 0;
        } else {
            $this->filter_branch_id = $this->userBranchId;
        }
    }

    protected function rules(): array
    {
        $rules = [
            'days_to_expiry_min' => 'required|integer|min:0|max:365',
            'days_to_expiry_max' => 'required|integer|min:0|max:365|gte:days_to_expiry_min',
            'discount_percentage' => 'required|integer|min:0|max:100',
            'status_label' => 'required|string|max:50',
            'is_active' => 'boolean',
        ];

        // Super admin must select a branch
        if ($this->isSuperAdmin) {
            $rules['filter_branch_id'] = 'required|integer|min:1|exists:branches,id';
        }

        return $rules;
    }

    #[Computed]
    public function discountRules()
    {
        $query = ClearanceDiscountRule::with('branch')->orderBy('days_to_expiry_min');

        // Super admin with "All Branches" selected (filter_branch_id = 0)
        if ($this->isSuperAdmin && $this->filter_branch_id === 0) {
            // Show all rules from all branches plus global rules
            return $query->get();
        }

        // Branch filter applied or non-super-admin
        $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;

        // Show branch-specific rules + global rules (branch_id = null)
        return $query->where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
                ->orWhereNull('branch_id');
        })->get();
    }

    #[Computed]
    public function branches()
    {
        if (! $this->isSuperAdmin) {
            return collect();
        }

        return Branch::orderBy('name')->get(['id', 'name']);
    }

    public function openModal(?int $id = null): void
    {
        if ($id) {
            $rule = ClearanceDiscountRule::find($id);
            if ($rule) {
                $this->editing_id = $id;
                $this->days_to_expiry_min = $rule->days_to_expiry_min;
                $this->days_to_expiry_max = $rule->days_to_expiry_max;
                $this->discount_percentage = $rule->discount_percentage;
                $this->status_label = $rule->status_label;
                $this->is_active = $rule->is_active;
            }
        } else {
            $this->reset(['editing_id', 'days_to_expiry_min', 'days_to_expiry_max', 'discount_percentage', 'status_label', 'is_active']);
            $this->is_active = true;
        }
        $this->show_modal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Check permissions
        if (! auth()->user()?->can('clearance.rules.create') && ! auth()->user()?->can('clearance.rules.edit')) {
            session()->flash('error', 'You do not have permission to create or edit discount rules.');
            return;
        }

        // Super admin must select a branch
        $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;
        
        if ($branchId <= 0) {
            session()->flash('error', 'Please select a branch to create the discount rule.');
            return;
        }

        ClearanceDiscountRule::updateOrCreate(
            ['id' => $this->editing_id],
            [
                'branch_id' => $branchId,
                'days_to_expiry_min' => $this->days_to_expiry_min,
                'days_to_expiry_max' => $this->days_to_expiry_max,
                'discount_percentage' => $this->discount_percentage,
                'status_label' => $this->status_label,
                'is_active' => $this->is_active,
            ]
        );

        ActivityLogger::log(
            $this->editing_id ? 'clearance_discount_rule_updated' : 'clearance_discount_rule_created',
            null,
            $this->editing_id ? "Updated discount rule: {$this->days_to_expiry_min}-{$this->days_to_expiry_max} days ({$this->discount_percentage}%)" : "Created discount rule: {$this->days_to_expiry_min}-{$this->days_to_expiry_max} days ({$this->discount_percentage}%)",
            ['days_min' => $this->days_to_expiry_min, 'days_max' => $this->days_to_expiry_max, 'discount_pct' => $this->discount_percentage, 'branch_id' => $branchId],
            $branchId
        );

        $this->show_modal = false;
        $this->reset(['editing_id', 'days_to_expiry_min', 'days_to_expiry_max', 'discount_percentage', 'status_label', 'is_active']);

        session()->flash('success', __('Discount rule saved successfully.'));
    }

    public function delete(int $id): void
    {
        $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;
        $rule = ClearanceDiscountRule::find($id);
        if ($rule && ($rule->branch_id === $branchId || $this->isSuperAdmin)) {
            ActivityLogger::log(
                'clearance_discount_rule_deleted',
                null,
                "Deleted discount rule: {$rule->days_to_expiry_min}-{$rule->days_to_expiry_max} days ({$rule->discount_percentage}%)",
                ['days_min' => $rule->days_to_expiry_min, 'days_max' => $rule->days_to_expiry_max, 'discount_pct' => $rule->discount_percentage],
                $branchId
            );
            $rule->delete();
            session()->flash('success', __('Discount rule deleted.'));
        }
    }

    public function toggleActive(int $id): void
    {
        $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;
        $rule = ClearanceDiscountRule::find($id);
        if ($rule && ($rule->branch_id === $branchId || $rule->branch_id === null || $this->isSuperAdmin)) {
            $rule->update(['is_active' => !$rule->is_active]);
        }
    }

    public function viewRule(int $id): void
    {
        $rule = ClearanceDiscountRule::with('branch')->find($id);
        if ($rule) {
            $this->viewing_id = $id;
            $this->days_to_expiry_min = $rule->days_to_expiry_min;
            $this->days_to_expiry_max = $rule->days_to_expiry_max;
            $this->discount_percentage = $rule->discount_percentage;
            $this->status_label = $rule->status_label;
            $this->is_active = $rule->is_active;
            $this->show_view_modal = true;
        }
    }

    public function closeViewModal(): void
    {
        $this->show_view_modal = false;
        $this->viewing_id = null;
        $this->reset(['days_to_expiry_min', 'days_to_expiry_max', 'discount_percentage', 'status_label', 'is_active']);
    }

    public function confirmDelete(int $id): void
    {
        $rule = ClearanceDiscountRule::with('branch')->find($id);
        if ($rule) {
            $this->deleting_id = $id;
            $this->deleting_rule_info = "{$rule->days_to_expiry_min}-{$rule->days_to_expiry_max} days ({$rule->discount_percentage}%) - " . ($rule->branch?->name ?? 'Global');
            $this->show_delete_modal = true;
        }
    }

    public function performDelete(): void
    {
        if (! $this->deleting_id) {
            return;
        }

        // Check delete permission
        if (! auth()->user()?->can('clearance.rules.delete')) {
            session()->flash('error', 'You do not have permission to delete discount rules.');
            $this->show_delete_modal = false;
            return;
        }

        $rule = ClearanceDiscountRule::find($this->deleting_id);
        if ($rule) {
            $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;
            
            ActivityLogger::log(
                'clearance_discount_rule_deleted',
                null,
                "Deleted discount rule: {$rule->days_to_expiry_min}-{$rule->days_to_expiry_max} days ({$rule->discount_percentage}%)",
                ['days_min' => $rule->days_to_expiry_min, 'days_max' => $rule->days_to_expiry_max, 'discount_pct' => $rule->discount_percentage],
                $branchId
            );
            
            $rule->delete();
            session()->flash('success', __('Discount rule deleted.'));
        }

        $this->show_delete_modal = false;
        $this->deleting_id = null;
        $this->deleting_rule_info = '';
    }

    public function closeDeleteModal(): void
    {
        $this->show_delete_modal = false;
        $this->deleting_id = null;
        $this->deleting_rule_info = '';
    }

    public function render()
    {
        return view('livewire.clearance.clearance-discount-rules');
    }
}
