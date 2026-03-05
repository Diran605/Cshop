<?php

namespace App\Livewire\Clearance;

use App\Models\Branch;
use App\Models\ClearanceAction;
use App\Models\ClearanceItem;
use App\Models\ClearanceSale;
use App\Models\Donation;
use App\Models\Disposal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ClearanceReports extends Component
{
    public string $date_from;
    public string $date_to;
    public string $filter_action = 'all';

    public bool $isSuperAdmin = false;
    public int $filter_branch_id = 0;
    public int $userBranchId = 0;

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

        $this->date_from = Carbon::today()->startOfMonth()->toDateString();
        $this->date_to = Carbon::today()->toDateString();
    }

    public function updatedFilterBranchId(): void
    {
        // Reset computed properties
    }

    #[Computed]
    public function stats()
    {
        $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;
        $from = Carbon::parse($this->date_from)->startOfDay();
        $to = Carbon::parse($this->date_to)->endOfDay();

        // Total items processed
        $totalItems = ClearanceAction::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$from, $to])
            ->sum('quantity');

        // Total original value
        $totalOriginalValue = ClearanceAction::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$from, $to])
            ->sum('original_value');

        // Total recovered value
        $totalRecoveredValue = ClearanceAction::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$from, $to])
            ->sum('recovered_value');

        // Total loss value
        $totalLossValue = ClearanceAction::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$from, $to])
            ->sum('loss_value');

        // Recovery rate
        $recoveryRate = $totalOriginalValue > 0 
            ? round(($totalRecoveredValue / $totalOriginalValue) * 100, 1) 
            : 0;

        // By action type
        $byType = ClearanceAction::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$from, $to])
            ->select('action_type', DB::raw('SUM(quantity) as qty'), DB::raw('SUM(original_value) as original'), DB::raw('SUM(recovered_value) as recovered'), DB::raw('SUM(loss_value) as loss'))
            ->groupBy('action_type')
            ->get()
            ->keyBy('action_type');

        return [
            'total_items' => (int) $totalItems,
            'total_original_value' => (float) $totalOriginalValue,
            'total_recovered_value' => (float) $totalRecoveredValue,
            'total_loss_value' => (float) $totalLossValue,
            'recovery_rate' => $recoveryRate,
            'by_type' => $byType,
        ];
    }

    #[Computed]
    public function recentActions()
    {
        $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;
        $from = Carbon::parse($this->date_from)->startOfDay();
        $to = Carbon::parse($this->date_to)->endOfDay();

        return ClearanceAction::with(['clearanceItem.product', 'user'])
            ->when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$from, $to])
            ->when($this->filter_action !== 'all', fn ($q) => $q->where('action_type', $this->filter_action))
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();
    }

    #[Computed]
    public function topProducts()
    {
        $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;
        $from = Carbon::parse($this->date_from)->startOfDay();
        $to = Carbon::parse($this->date_to)->endOfDay();

        return ClearanceAction::with(['clearanceItem.product'])
            ->when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$from, $to])
            ->select('clearance_item_id', DB::raw('SUM(quantity) as total_qty'), DB::raw('SUM(original_value) as total_original'), DB::raw('SUM(recovered_value) as total_recovered'))
            ->groupBy('clearance_item_id')
            ->orderByDesc('total_qty')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function dailyTrend()
    {
        $branchId = $this->isSuperAdmin && $this->filter_branch_id > 0 ? $this->filter_branch_id : $this->userBranchId;
        $from = Carbon::parse($this->date_from)->startOfDay();
        $to = Carbon::parse($this->date_to)->endOfDay();

        return ClearanceAction::when($branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->whereBetween('created_at', [$from, $to])
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(quantity) as qty'), DB::raw('SUM(recovered_value) as recovered'), DB::raw('SUM(loss_value) as loss'))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
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

    public function render()
    {
        return view('livewire.clearance.clearance-reports');
    }
}
