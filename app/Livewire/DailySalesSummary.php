<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\SalesItem;
use App\Models\SalesReceipt;
use Carbon\Carbon;
use Livewire\Component;

class DailySalesSummary extends Component
{
    public string $summary_date;
    public int $branch_id = 0;

    public bool $isSuperAdmin = false;
    public int $auth_user_id = 0;

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

        $this->summary_date = now()->format('Y-m-d');
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

    public function previousDay(): void
    {
        $this->summary_date = Carbon::parse($this->summary_date)->subDay()->format('Y-m-d');
    }

    public function nextDay(): void
    {
        $this->summary_date = Carbon::parse($this->summary_date)->addDay()->format('Y-m-d');
    }

    public function today(): void
    {
        $this->summary_date = now()->format('Y-m-d');
    }

    public function render()
    {
        $this->syncAuthContext();

        $date = Carbon::parse($this->summary_date);
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // Base query for the day
        $salesQuery = SalesReceipt::query()
            ->whereBetween('sold_at', [$startOfDay, $endOfDay])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id));

        // Clone for different aggregations
        $totalSales = (clone $salesQuery)->whereNull('voided_at')->count();
        $voidedSales = (clone $salesQuery)->whereNotNull('voided_at')->count();

        $totalRevenue = (clone $salesQuery)
            ->whereNull('voided_at')
            ->sum('grand_total');

        $totalCost = (clone $salesQuery)
            ->whereNull('voided_at')
            ->sum('cogs_total');

        $totalProfit = (clone $salesQuery)
            ->whereNull('voided_at')
            ->sum('profit_total');

        // Daily Expenses
        $totalExpenses = \App\Models\Expense::query()
            ->whereDate('expense_date', $date->toDateString())
            ->whereNull('voided_at')
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->sum('amount');

        $netProfit = $totalProfit - $totalExpenses;

        $totalPaid = (clone $salesQuery)
            ->whereNull('voided_at')
            ->sum('amount_paid');

        // Sales by payment method
        $salesByPayment = (clone $salesQuery)
            ->whereNull('voided_at')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(grand_total) as total')
            ->groupBy('payment_method')
            ->get()
            ->keyBy('payment_method');

        // Hourly breakdown
        $hourlySales = (clone $salesQuery)
            ->whereNull('voided_at')
            ->selectRaw('HOUR(sold_at) as hour, COUNT(*) as count, SUM(grand_total) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->keyBy('hour');

        // Top selling products
        $topProducts = SalesItem::query()
            ->whereHas('receipt', function ($q) use ($startOfDay, $endOfDay) {
                $q->whereBetween('sold_at', [$startOfDay, $endOfDay])
                    ->whereNull('voided_at')
                    ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
                    ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id));
            })
            ->selectRaw('product_id, SUM(quantity) as total_qty, SUM(line_total) as total_revenue')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->with('product')
            ->limit(10)
            ->get();

        // Recent sales
        $recentSales = (clone $salesQuery)
            ->with(['branch', 'user'])
            ->orderByDesc('sold_at')
            ->limit(10)
            ->get();

        // Branch comparison (for super admin)
        $branchComparison = collect();
        if ($this->isSuperAdmin && $this->branch_id === 0) {
            $branchComparison = Branch::query()
                ->where('is_active', true)
                ->withSum(['salesReceipts as total_revenue' => function ($q) use ($startOfDay, $endOfDay) {
                    $q->whereBetween('sold_at', [$startOfDay, $endOfDay])
                        ->whereNull('voided_at');
                }], 'grand_total')
                ->withCount(['salesReceipts as total_sales' => function ($q) use ($startOfDay, $endOfDay) {
                    $q->whereBetween('sold_at', [$startOfDay, $endOfDay])
                        ->whereNull('voided_at');
                }])
                ->orderByDesc('total_revenue')
                ->get();
        }

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.daily-sales-summary', [
            'totalSales' => $totalSales,
            'voidedSales' => $voidedSales,
            'totalRevenue' => $totalRevenue,
            'totalCost' => $totalCost,
            'totalProfit' => $totalProfit,
            'totalExpenses' => (float) $totalExpenses,
            'netProfit' => (float) $netProfit,
            'totalPaid' => $totalPaid,
            'salesByPayment' => $salesByPayment,
            'hourlySales' => $hourlySales,
            'topProducts' => $topProducts,
            'recentSales' => $recentSales,
            'branchComparison' => $branchComparison,
            'branches' => $branches,
            'date' => $date,
        ]);
    }
}
