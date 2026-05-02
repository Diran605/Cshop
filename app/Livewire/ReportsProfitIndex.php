<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Expense;
use App\Models\Product;
use App\Models\SalesItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportsProfitIndex extends Component
{
    public int $branch_id = 0;
    public string $date_from;
    public string $date_to;

    public string $search = '';

    public int $category_id = 0;
    public int $product_filter_id = 0;
    public string $sale_mode = 'all';

    public bool $isSuperAdmin = false;
    #[Locked]
    public array $profitByDay = [];
    #[Locked]
    public array $categoryProfit = [];

    public function mount(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->auth_user_id = (int) ($user?->id ?? 0);

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        } else {
            $this->branch_id = 0;
        }

        $today = Carbon::today();
        $this->date_from = $today->copy()->startOfMonth()->toDateString();
        $this->date_to = $today->toDateString();

        $this->category_id = 0;
        $this->product_filter_id = 0;
        $this->sale_mode = 'all';
        $this->search = '';
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;

            $today = Carbon::today();
            $this->date_from = $today->copy()->startOfMonth()->toDateString();
            $this->date_to = $today->toDateString();

            $this->category_id = 0;
            $this->product_filter_id = 0;
            $this->sale_mode = 'all';
            $this->search = '';
        }

        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
    }

    public function updatedDateFrom(): void { $this->dispatch('updateCharts'); }
    public function updatedDateTo(): void { $this->dispatch('updateCharts'); }
    public function updatedCategoryId(): void { $this->dispatch('updateCharts'); }

    public function render()
    {
        $this->syncAuthContext();

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) (auth()->user()?->branch_id ?? 0);
            $branches = Branch::query()
                ->whereKey($this->branch_id)
                ->where('is_active', true)
                ->get();
        } else {
            $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();
        }

        $from = Carbon::parse($this->date_from)->startOfDay();
        $to = Carbon::parse($this->date_to)->endOfDay();

        // Previous period calculation
        $diff = $from->diffInDays($to);
        $prevFrom = $from->copy()->subDays($diff + 1);
        $prevTo = $from->copy()->subDay()->endOfDay();

        $categories = Category::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->get();

        $salesItemsBase = SalesItem::query()
            ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
            ->join('products', 'products.id', '=', 'sales_items.product_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('sales_receipts.branch_id', $this->branch_id))
            ->whereNull('sales_receipts.voided_at')
            ->whereBetween('sales_receipts.sold_at', [$from, $to])
            ->when($this->category_id > 0, fn ($q) => $q->where('products.category_id', $this->category_id));

        $summaryRow = (clone $salesItemsBase)
            ->select([
                DB::raw('SUM(sales_items.line_total) as sales_total'),
                DB::raw('SUM(sales_items.line_profit) as profit_total'),
            ])
            ->first();

        $salesTotal = (float) ($summaryRow?->sales_total ?? 0);
        $grossProfit = (float) ($summaryRow?->profit_total ?? 0);
        $grossMargin = $salesTotal > 0 ? (($grossProfit / $salesTotal) * 100) : 0;

        // Expenses for current period
        $expenseTotal = Expense::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->whereNull('voided_at')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
        $expenseTotal = (float) ($expenseTotal ?? 0);

        $netProfit = $grossProfit - $expenseTotal;
        $netMargin = $salesTotal > 0 ? (($netProfit / $salesTotal) * 100) : 0;

        // Previous period summary
        $prevSalesItemsBase = SalesItem::query()
            ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
            ->join('products', 'products.id', '=', 'sales_items.product_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('sales_receipts.branch_id', $this->branch_id))
            ->whereNull('sales_receipts.voided_at')
            ->whereBetween('sales_receipts.sold_at', [$prevFrom, $prevTo])
            ->when($this->category_id > 0, fn ($q) => $q->where('products.category_id', $this->category_id));

        $prevSummaryRow = $prevSalesItemsBase
            ->select([
                DB::raw('SUM(sales_items.line_total) as sales_total'),
                DB::raw('SUM(sales_items.line_profit) as profit_total'),
            ])
            ->first();

        $prevSalesTotal = (float) ($prevSummaryRow?->sales_total ?? 0);
        $prevGrossProfit = (float) ($prevSummaryRow?->profit_total ?? 0);

        // Previous expenses
        $prevExpenseTotal = Expense::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->whereNull('voided_at')
            ->whereBetween('expense_date', [$prevFrom->toDateString(), $prevTo->toDateString()])
            ->sum('amount');
        $prevExpenseTotal = (float) ($prevExpenseTotal ?? 0);

        $prevNetProfit = $prevGrossProfit - $prevExpenseTotal;

        // Changes
        $grossProfitChange = $prevGrossProfit > 0 ? (($grossProfit - $prevGrossProfit) / $prevGrossProfit) * 100 : 0;
        $netProfitChange = $prevNetProfit != 0 ? (($netProfit - $prevNetProfit) / abs($prevNetProfit)) * 100 : 0;
        $expenseChange = $prevExpenseTotal > 0 ? (($expenseTotal - $prevExpenseTotal) / $prevExpenseTotal) * 100 : 0;
        
        $prevGrossMargin = $prevSalesTotal > 0 ? (($prevGrossProfit / $prevSalesTotal) * 100) : 0;
        $marginChange = $grossMargin - $prevGrossMargin;

        // Profit Trend (Line Chart)
        $profitByDayRaw = (clone $salesItemsBase)
            ->groupBy(DB::raw('DATE(sales_receipts.sold_at)'))
            ->get([
                DB::raw('DATE(sales_receipts.sold_at) as day'),
                DB::raw('SUM(sales_items.line_profit) as profit_total'),
                DB::raw('SUM(sales_items.line_total) as sales_total'),
            ]);

        $expensesByDayRaw = Expense::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->whereNull('voided_at')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('expense_date')
            ->get([
                'expense_date as day',
                DB::raw('SUM(amount) as amount_total'),
            ]);

        $prevProfitByDayRaw = SalesItem::query()
            ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
            ->join('products', 'products.id', '=', 'sales_items.product_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('sales_receipts.branch_id', $this->branch_id))
            ->whereNull('sales_receipts.voided_at')
            ->whereBetween('sales_receipts.sold_at', [$prevFrom, $prevTo])
            ->when($this->category_id > 0, fn ($q) => $q->where('products.category_id', $this->category_id))
            ->groupBy(DB::raw('DATE(sales_receipts.sold_at)'))
            ->get([
                DB::raw('DATE(sales_receipts.sold_at) as day'),
                DB::raw('SUM(sales_items.line_profit) as profit_total'),
            ]);

        $profitByDay = [];
        $daysCount = $from->diffInDays($to) + 1;
        for ($i = 0; $i < $daysCount; $i++) {
            $currentDay = $from->copy()->addDays($i)->toDateString();
            $prevDay = $prevFrom->copy()->addDays($i)->toDateString();

            $grossProfitDay = (float) ($profitByDayRaw->firstWhere('day', $currentDay)->profit_total ?? 0);
            $expenseDay = (float) ($expensesByDayRaw->firstWhere('day', $currentDay)->amount_total ?? 0);

            $profitByDay[] = [
                'day' => Carbon::parse($currentDay)->format('M d'),
                'profit' => $grossProfitDay,
                'net_profit' => $grossProfitDay - $expenseDay,
                'revenue' => (float) ($profitByDayRaw->firstWhere('day', $currentDay)->sales_total ?? 0),
                'prev_profit' => (float) ($prevProfitByDayRaw->firstWhere('day', $prevDay)->profit_total ?? 0),
            ];
        }

        $this->profitByDay = $profitByDay;

        // Category Profitability (Bar Chart)
        $categoryProfit = SalesItem::query()
            ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
            ->join('products', 'products.id', '=', 'sales_items.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('sales_receipts.branch_id', $this->branch_id))
            ->whereNull('sales_receipts.voided_at')
            ->whereBetween('sales_receipts.sold_at', [$from, $to])
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('profit_total')
            ->limit(10)
            ->get([
                'categories.name',
                DB::raw('SUM(sales_items.line_profit) as profit_total'),
                DB::raw('SUM(sales_items.line_total) as sales_total'),
                DB::raw('(SUM(sales_items.line_profit) / SUM(sales_items.line_total) * 100) as margin'),
            ])->toArray();

        $this->categoryProfit = $categoryProfit;

        // Top Products by Profit (Table)
        $topProductsByProfit = (clone $salesItemsBase)
            ->groupBy('sales_items.product_id', 'products.name')
            ->orderByDesc('profit_total')
            ->limit(10)
            ->get([
                'products.name as product_name',
                DB::raw('SUM(sales_items.quantity) as qty_sold'),
                DB::raw('SUM(sales_items.line_profit) as profit_total'),
                DB::raw('(SUM(sales_items.line_profit) / SUM(sales_items.line_total) * 100) as margin'),
            ]);

        return view('livewire.reports-profit-index', [
            'branches' => $branches,
            'categories' => $categories,
            'grossProfit' => $grossProfit,
            'grossProfitChange' => $grossProfitChange,
            'netProfit' => $netProfit,
            'netProfitChange' => $netProfitChange,
            'expenseTotal' => $expenseTotal,
            'expenseChange' => $expenseChange,
            'grossMargin' => $grossMargin,
            'marginChange' => $marginChange,
            'profitByDay' => $profitByDay,
            'categoryProfit' => $categoryProfit,
            'topProductsByProfit' => $topProductsByProfit,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }

    public function updatedBranchId(): void
    {
        $this->dispatch('updateCharts');
        if (! $this->isSuperAdmin) {
            return;
        }

        $this->category_id = 0;
        $this->product_filter_id = 0;
    }
}
