<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\SalesReceipt;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportsOverview extends Component
{
    public int $branch_id = 0;
    public int $year;
    public bool $isSuperAdmin = false;

    public function mount(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        
        if (!$this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        }

        $this->year = Carbon::now()->year;
    }

    public function prevYear(): void 
    { 
        $this->year--; 
        $this->dispatch('updated');
    }

    public function nextYear(): void 
    { 
        $this->year++; 
        $this->dispatch('updated');
    }

    public function updatedBranchId(): void
    {
        $this->dispatch('updated');
    }

    public function render()
    {
        $branches = Branch::where('is_active', true)->get();
        $data = $this->getOverviewData();

        return view('livewire.reports-overview', [
            'branches' => $branches,
            'data' => $data,
        ])->layout('layouts.app');
    }

    private function getOverviewData(): array
    {
        $start = Carbon::create($this->year, 1, 1)->startOfYear();
        $end = Carbon::create($this->year, 12, 31)->endOfYear();

        // Previous year for trend comparison
        $prevStart = (clone $start)->subYear();
        $prevEnd = (clone $end)->subYear();

        // Get current branch name for printing
        $branchName = __('All Branches');
        if ($this->branch_id > 0) {
            $branchName = Branch::find($this->branch_id)?->name ?? $branchName;
        }

        // Sales and Profit (Current Year)
        $sales = SalesReceipt::whereNull('voided_at')
            ->when($this->branch_id > 0, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('sold_at', [$start, $end])
            ->select(
                DB::raw('MONTH(sold_at) as month'),
                DB::raw('SUM(grand_total) as total_sales'),
                DB::raw('SUM(profit_total) as total_profit')
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // Sales and Profit (Previous Year)
        $prevSales = SalesReceipt::whereNull('voided_at')
            ->when($this->branch_id > 0, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('sold_at', [$prevStart, $prevEnd])
            ->select(
                DB::raw('MONTH(sold_at) as month'),
                DB::raw('SUM(grand_total) as total_sales')
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // Expenses (Current Year)
        $expenses = Expense::whereNull('voided_at')
            ->when($this->branch_id > 0, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('expense_date', [$start->toDateString(), $end->toDateString()])
            ->select(
                DB::raw('MONTH(expense_date) as month'),
                DB::raw('SUM(amount) as total_expenses')
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        // Inventory Losses (Current Year) - Disposals and Donations
        $inventoryLosses = \App\Models\ClearanceAction::where('status', \App\Models\ClearanceAction::STATUS_ACTIVE)
            ->whereIn('action_type', [\App\Models\ClearanceAction::ACTION_DISPOSE, \App\Models\ClearanceAction::ACTION_DONATE])
            ->when($this->branch_id > 0, fn($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('created_at', [$start, $end])
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(loss_value) as total_loss')
            )
            ->groupBy('month')
            ->get()
            ->keyBy('month');

        $monthlyData = [];
        for ($i = 1; $i <= 12; $i++) {
            $s = $sales->get($i);
            $ps = $prevSales->get($i);
            $e = $expenses->get($i);
            $l = $inventoryLosses->get($i);
            
            $salesVal = (float) ($s?->total_sales ?? 0);
            $prevSalesVal = (float) ($ps?->total_sales ?? 0);
            $profitVal = (float) ($s?->total_profit ?? 0);
            $expenseVal = (float) ($e?->total_expenses ?? 0);
            $lossVal = (float) ($l?->total_loss ?? 0);
            
            // Adjusted Gross Profit = Gross Profit - Inventory Loss
            $adjustedGrossProfit = $profitVal - $lossVal;
            
            // Monthly Trend Calculation
            $trend = $prevSalesVal > 0 ? (($salesVal - $prevSalesVal) / $prevSalesVal) * 100 : 0;
            
            $monthlyData[$i] = [
                'name' => Carbon::create()->month($i)->format('F'),
                'sales' => $salesVal,
                'prev_sales' => $prevSalesVal,
                'trend' => $trend,
                'profit' => $profitVal,
                'inventory_loss' => $lossVal,
                'adjusted_profit' => $adjustedGrossProfit,
                'expenses' => $expenseVal,
                'net' => $adjustedGrossProfit - $expenseVal,
            ];
        }

        return [
            'monthly' => $monthlyData,
            'year' => $this->year,
            'branch_name' => $branchName,
            'total_sales' => array_sum(array_column($monthlyData, 'sales')),
            'total_profit' => array_sum(array_column($monthlyData, 'profit')),
            'total_inventory_loss' => array_sum(array_column($monthlyData, 'inventory_loss')),
            'total_adjusted_profit' => array_sum(array_column($monthlyData, 'adjusted_profit')),
            'total_expenses' => array_sum(array_column($monthlyData, 'expenses')),
            'total_net' => array_sum(array_column($monthlyData, 'net')),
        ];
    }
}
