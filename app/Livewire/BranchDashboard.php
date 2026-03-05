<?php

namespace App\Livewire;

use App\Models\ClearanceItem;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesReceipt;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BranchDashboard extends Component
{
    public int $branchId = 0;

    public function mount(): void
    {
        $this->branchId = (int) (auth()->user()?->branch_id ?? 0);
    }

    // ========== CLEARANCE ==========

    public function getClearanceCountProperty(): int
    {
        return ClearanceItem::where('branch_id', $this->branchId)
            ->where('status', '!=', 'actioned')
            ->count();
    }

    public function getHasClearancePermissionProperty(): bool
    {
        return auth()->user()?->can('clearance.view') ?? false;
    }

    // ========== TODAY'S KEY METRICS ==========

    public function getTodayStatsProperty(): array
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Today's sales
        $todaySales = (float) SalesReceipt::query()
            ->where('branch_id', $this->branchId)
            ->whereNull('voided_at')
            ->whereDate('sold_at', $today)
            ->sum('grand_total');

        // Yesterday's sales for comparison
        $yesterdaySales = (float) SalesReceipt::query()
            ->where('branch_id', $this->branchId)
            ->whereNull('voided_at')
            ->whereDate('sold_at', $yesterday)
            ->sum('grand_total');

        // Today's profit
        $todayProfit = (float) SalesReceipt::query()
            ->where('branch_id', $this->branchId)
            ->whereNull('voided_at')
            ->whereDate('sold_at', $today)
            ->sum('profit_total');

        $yesterdayProfit = (float) SalesReceipt::query()
            ->where('branch_id', $this->branchId)
            ->whereNull('voided_at')
            ->whereDate('sold_at', $yesterday)
            ->sum('profit_total');

        // Today's transactions
        $todayTransactions = (int) SalesReceipt::query()
            ->where('branch_id', $this->branchId)
            ->whereNull('voided_at')
            ->whereDate('sold_at', $today)
            ->count();

        $yesterdayTransactions = (int) SalesReceipt::query()
            ->where('branch_id', $this->branchId)
            ->whereNull('voided_at')
            ->whereDate('sold_at', $yesterday)
            ->count();

        // Calculate percentage changes
        $salesChange = $yesterdaySales > 0 ? round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100, 1) : ($todaySales > 0 ? 100 : 0);
        $profitChange = $yesterdayProfit > 0 ? round((($todayProfit - $yesterdayProfit) / $yesterdayProfit) * 100, 1) : ($todayProfit > 0 ? 100 : 0);
        $transactionsChange = $yesterdayTransactions > 0 ? round((($todayTransactions - $yesterdayTransactions) / $yesterdayTransactions) * 100, 1) : ($todayTransactions > 0 ? 100 : 0);

        return [
            'sales' => $todaySales,
            'sales_change' => $salesChange,
            'profit' => $todayProfit,
            'profit_change' => $profitChange,
            'transactions' => $todayTransactions,
            'transactions_change' => $transactionsChange,
        ];
    }

    public function getStockStatsProperty(): array
    {
        $stockValue = (float) ProductStock::query()
            ->where('branch_id', $this->branchId)
            ->sum(DB::raw('COALESCE(current_stock, 0) * COALESCE(cost_price, 0)'));

        $stockItems = (int) ProductStock::query()
            ->where('branch_id', $this->branchId)
            ->where('current_stock', '>', 0)
            ->count();

        $lowStockCount = (int) ProductStock::query()
            ->where('branch_id', $this->branchId)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('current_stock', '>', 0)
            ->count();

        return [
            'value' => $stockValue,
            'items' => $stockItems,
            'low_stock_count' => $lowStockCount,
        ];
    }

    // ========== ALERTS & NOTIFICATIONS ==========

    public function getLowStockItemsProperty(): \Illuminate\Support\Collection
    {
        return ProductStock::query()
            ->with(['product'])
            ->where('branch_id', $this->branchId)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('current_stock', '>', 0)
            ->orderBy('current_stock')
            ->limit(5)
            ->get();
    }

    public function getExpiringProductsProperty(): \Illuminate\Support\Collection
    {
        return DB::table('stock_in_items')
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->join('products', 'products.id', '=', 'stock_in_items.product_id')
            ->where('stock_in_receipts.branch_id', $this->branchId)
            ->whereNull('stock_in_receipts.voided_at')
            ->where('stock_in_items.remaining_quantity', '>', 0)
            ->whereNotNull('stock_in_items.expiry_date')
            ->where('stock_in_items.expiry_date', '>=', Carbon::today())
            ->where('stock_in_items.expiry_date', '<=', Carbon::today()->addDays(7))
            ->orderBy('stock_in_items.expiry_date')
            ->limit(5)
            ->get([
                'products.name as product_name',
                'stock_in_items.expiry_date',
                'stock_in_items.remaining_quantity',
            ]);
    }

    public function getRecentActivityProperty(): \Illuminate\Support\Collection
    {
        $sales = SalesReceipt::query()
            ->with(['user'])
            ->where('branch_id', $this->branchId)
            ->whereNull('voided_at')
            ->orderByDesc('sold_at')
            ->limit(3)
            ->get()
            ->map(fn ($s) => [
                'type' => 'sale',
                'description' => "Sale #{$s->receipt_no}",
                'amount' => $s->grand_total,
                'user' => $s->user?->name ?? 'Unknown',
                'timestamp' => $s->sold_at,
            ]);

        $stockIns = StockMovement::query()
            ->with(['user'])
            ->where('branch_id', $this->branchId)
            ->where('movement_type', 'in')
            ->orderByDesc('created_at')
            ->limit(2)
            ->get()
            ->map(fn ($s) => [
                'type' => 'stock_in',
                'description' => 'Stock Delivery',
                'amount' => null,
                'user' => $s->user?->name ?? 'Unknown',
                'timestamp' => $s->created_at,
            ]);

        return $sales->concat($stockIns)->sortByDesc('timestamp')->take(5)->values();
    }

    // ========== SALES TREND (7 DAYS) ==========

    public function getSalesTrendProperty(): array
    {
        $data = SalesReceipt::query()
            ->where('branch_id', $this->branchId)
            ->whereNull('voided_at')
            ->where('sold_at', '>=', Carbon::today()->subDays(6))
            ->groupBy(DB::raw('DATE(sold_at)'))
            ->orderBy(DB::raw('DATE(sold_at)'))
            ->get([
                DB::raw('DATE(sold_at) as date'),
                DB::raw('SUM(grand_total) as total'),
            ]);

        $labels = [];
        $values = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::today()->subDays($i)->format('D');
            $row = $data->firstWhere('date', $date);
            $values[] = (float) ($row?->total ?? 0);
        }

        return [
            'labels' => $labels,
            'data' => $values,
        ];
    }

    // ========== TOP PERFORMING PRODUCTS ==========

    public function getTopProductsProperty(): \Illuminate\Support\Collection
    {
        return DB::table('sales_items')
            ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
            ->join('products', 'products.id', '=', 'sales_items.product_id')
            ->where('sales_receipts.branch_id', $this->branchId)
            ->whereNull('sales_receipts.voided_at')
            ->where('sales_receipts.sold_at', '>=', Carbon::now()->startOfWeek())
            ->groupBy('products.id', 'products.name')
            ->orderByDesc(DB::raw('SUM(sales_items.line_total)'))
            ->limit(5)
            ->get([
                'products.id',
                'products.name',
                DB::raw('SUM(sales_items.quantity) as qty_sold'),
                DB::raw('SUM(sales_items.line_total) as revenue'),
            ]);
    }

    // ========== PROFIT SUMMARY (THIS MONTH) ==========

    public function getProfitSummaryProperty(): array
    {
        $monthStart = Carbon::now()->startOfMonth();

        // Gross Revenue
        $grossRevenue = (float) SalesReceipt::query()
            ->where('branch_id', $this->branchId)
            ->whereNull('voided_at')
            ->where('sold_at', '>=', $monthStart)
            ->sum('grand_total');

        // COGS
        $cogs = (float) SalesReceipt::query()
            ->where('branch_id', $this->branchId)
            ->whereNull('voided_at')
            ->where('sold_at', '>=', $monthStart)
            ->sum('cogs_total');

        // Gross Profit
        $grossProfit = $grossRevenue - $cogs;

        // Operating Expenses (from expenses table if exists)
        $expenses = 0;
        if (\Schema::hasTable('expenses')) {
            $expenses = (float) DB::table('expenses')
                ->where('branch_id', $this->branchId)
                ->where('expense_date', '>=', $monthStart)
                ->sum('amount');
        }

        $netProfit = $grossProfit - $expenses;

        $grossMargin = $grossRevenue > 0 ? round(($grossProfit / $grossRevenue) * 100, 1) : 0;
        $netMargin = $grossRevenue > 0 ? round(($netProfit / $grossRevenue) * 100, 1) : 0;

        return [
            'gross_revenue' => $grossRevenue,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'expenses' => $expenses,
            'net_profit' => $netProfit,
            'gross_margin' => $grossMargin,
            'net_margin' => $netMargin,
        ];
    }

    // ========== RECENT SALES ==========

    public function getRecentSalesProperty(): \Illuminate\Support\Collection
    {
        return SalesReceipt::query()
            ->with(['user'])
            ->where('branch_id', $this->branchId)
            ->whereNull('voided_at')
            ->orderByDesc('sold_at')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.branch-dashboard');
    }
}
