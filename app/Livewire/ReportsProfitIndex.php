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
    public int $auth_user_id = 0;

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

        $categories = Category::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->get();

        $productsForFilter = Product::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->get();

        $salesItemsBase = SalesItem::query()
            ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
            ->join('products', 'products.id', '=', 'sales_items.product_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('sales_receipts.branch_id', $this->branch_id))
            ->whereNull('sales_receipts.voided_at')
            ->whereBetween('sales_receipts.sold_at', [$from, $to]);

        if ($this->category_id > 0) {
            $salesItemsBase->where('products.category_id', $this->category_id);
        }
        if ($this->product_filter_id > 0) {
            $salesItemsBase->where('sales_items.product_id', $this->product_filter_id);
        }
        if ($this->sale_mode === 'unit') {
            $salesItemsBase->where('sales_items.entry_mode', 'unit');
        } elseif ($this->sale_mode === 'bulk') {
            $salesItemsBase->where('sales_items.entry_mode', 'bulk');
        }

        $summaryRow = (clone $salesItemsBase)
            ->select([
                DB::raw('COUNT(DISTINCT sales_items.sales_receipt_id) as sales_count'),
                DB::raw('SUM(sales_items.line_total) as sales_total'),
                DB::raw('SUM(sales_items.line_cost) as cogs_total'),
                DB::raw('SUM(sales_items.line_profit) as profit_total'),
                DB::raw('SUM(CASE WHEN sales_items.is_low_profit = 1 THEN 1 ELSE 0 END) as low_profit_lines'),
                DB::raw('SUM(CASE WHEN sales_items.is_loss = 1 THEN 1 ELSE 0 END) as loss_lines'),
            ])
            ->first();

        $salesCount = (int) ($summaryRow?->sales_count ?? 0);
        $salesTotal = (float) ($summaryRow?->sales_total ?? 0);
        $cogsTotal = (float) ($summaryRow?->cogs_total ?? 0);
        $profitTotal = (float) ($summaryRow?->profit_total ?? 0);
        $profitMargin = $salesTotal > 0 ? (($profitTotal / $salesTotal) * 100.0) : 0.0;

        $lowProfitLines = (int) ($summaryRow?->low_profit_lines ?? 0);
        $lossLines = (int) ($summaryRow?->loss_lines ?? 0);

        // Get expenses for the same period
        $expenseTotal = Expense::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->whereNull('voided_at')
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()])
            ->sum('amount');
        $expenseTotal = (float) ($expenseTotal ?? 0);

        // Calculate net profit (Gross Profit - Expenses)
        $netProfit = $profitTotal - $expenseTotal;
        $netProfitMargin = $salesTotal > 0 ? (($netProfit / $salesTotal) * 100.0) : 0.0;

        $profitByDay = (clone $salesItemsBase)
            ->groupBy(DB::raw('DATE(sales_receipts.sold_at)'))
            ->orderBy(DB::raw('DATE(sales_receipts.sold_at)'))
            ->get([
                DB::raw('DATE(sales_receipts.sold_at) as day'),
                DB::raw('SUM(sales_items.line_total) as sales_total'),
                DB::raw('SUM(sales_items.line_cost) as cogs_total'),
                DB::raw('SUM(sales_items.line_profit) as profit_total'),
            ]);

        $topProductsByProfit = (clone $salesItemsBase)
            ->groupBy('sales_items.product_id', 'products.name')
            ->orderByDesc('profit_total')
            ->limit(15)
            ->get([
                'products.name as product_name',
                DB::raw('SUM(sales_items.quantity) as qty_sold'),
                DB::raw('SUM(sales_items.line_total) as sales_total'),
                DB::raw('SUM(sales_items.line_cost) as cogs_total'),
                DB::raw('SUM(sales_items.line_profit) as profit_total'),
            ]);

        if (trim($this->search) !== '') {
            $term = strtolower(trim($this->search));
            $topProductsByProfit = $topProductsByProfit->filter(fn ($r) => str_contains(strtolower((string) $r->product_name), $term));
        }

        $branchesByProfit = collect();
        if ($this->isSuperAdmin && $this->branch_id <= 0) {
            $branchesByProfit = SalesItem::query()
                ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
                ->join('branches', 'branches.id', '=', 'sales_receipts.branch_id')
                ->whereNull('sales_receipts.voided_at')
                ->whereBetween('sales_receipts.sold_at', [$from, $to])
                ->groupBy('sales_receipts.branch_id', 'branches.name')
                ->orderByDesc('profit_total')
                ->limit(15)
                ->get([
                    'branches.name as branch_name',
                    DB::raw('SUM(sales_items.line_total) as sales_total'),
                    DB::raw('SUM(sales_items.line_cost) as cogs_total'),
                    DB::raw('SUM(sales_items.line_profit) as profit_total'),
                ]);
        }

        return view('livewire.reports-profit-index', [
            'branches' => $branches,
            'categories' => $categories,
            'productsForFilter' => $productsForFilter,
            'salesCount' => $salesCount,
            'salesTotal' => $salesTotal,
            'cogsTotal' => $cogsTotal,
            'profitTotal' => $profitTotal,
            'profitMargin' => $profitMargin,
            'expenseTotal' => $expenseTotal,
            'netProfit' => $netProfit,
            'netProfitMargin' => $netProfitMargin,
            'lowProfitLines' => $lowProfitLines,
            'lossLines' => $lossLines,
            'profitByDay' => $profitByDay,
            'topProductsByProfit' => $topProductsByProfit,
            'branchesByProfit' => $branchesByProfit,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }

    public function updatedBranchId(): void
    {
        if (! $this->isSuperAdmin) {
            return;
        }

        $this->category_id = 0;
        $this->product_filter_id = 0;
    }
}
