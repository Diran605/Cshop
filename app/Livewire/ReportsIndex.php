<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesItem;
use App\Models\SalesReceipt;
use App\Models\StockInItem;
use App\Models\StockInReceipt;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportsIndex extends Component
{
    public int $branch_id = 0;
    public string $date_from;
    public string $date_to;
    public bool $low_stock_only = false;

    public string $search = '';

    public int $category_id = 0;
    public int $product_filter_id = 0;
    public string $sale_mode = 'all';

    public string $trend_granularity = 'day';

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
        $this->low_stock_only = false;

        $this->category_id = 0;
        $this->product_filter_id = 0;
        $this->sale_mode = 'all';
        $this->trend_granularity = 'day';
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
            $this->low_stock_only = false;
            $this->category_id = 0;
            $this->product_filter_id = 0;
            $this->sale_mode = 'all';
            $this->trend_granularity = 'day';
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
            ->whereBetween('sales_receipts.sold_at', [$from, $to]);

        if ($this->category_id > 0) {
            $salesItemsBase->where('products.category_id', $this->category_id);
        }

        $summaryRow = (clone $salesItemsBase)
            ->select([
                DB::raw('COUNT(DISTINCT sales_items.sales_receipt_id) as sales_count'),
                DB::raw('SUM(sales_items.line_total) as sales_total'),
                DB::raw('SUM(sales_items.quantity) as items_sold'),
            ])
            ->first();

        $salesCount = (int) ($summaryRow?->sales_count ?? 0);
        $salesTotal = (float) ($summaryRow?->sales_total ?? 0);
        $itemsSold = (int) ($summaryRow?->items_sold ?? 0);
        $avgTransaction = $salesCount > 0 ? ($salesTotal / $salesCount) : 0.0;

        // Previous period summary
        $prevSummaryRow = SalesItem::query()
            ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
            ->join('products', 'products.id', '=', 'sales_items.product_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('sales_receipts.branch_id', $this->branch_id))
            ->whereNull('sales_receipts.voided_at')
            ->whereBetween('sales_receipts.sold_at', [$prevFrom, $prevTo])
            ->when($this->category_id > 0, fn ($q) => $q->where('products.category_id', $this->category_id))
            ->select([
                DB::raw('COUNT(DISTINCT sales_items.sales_receipt_id) as sales_count'),
                DB::raw('SUM(sales_items.line_total) as sales_total'),
                DB::raw('SUM(sales_items.quantity) as items_sold'),
            ])
            ->first();

        $prevSalesTotal = (float) ($prevSummaryRow?->sales_total ?? 0);
        $prevSalesCount = (int) ($prevSummaryRow?->sales_count ?? 0);
        $prevItemsSold = (int) ($prevSummaryRow?->items_sold ?? 0);
        $prevAvgTransaction = $prevSalesCount > 0 ? ($prevSalesTotal / $prevSalesCount) : 0.0;

        $salesChange = $prevSalesTotal > 0 ? (($salesTotal - $prevSalesTotal) / $prevSalesTotal) * 100 : 0;
        $countChange = $prevSalesCount > 0 ? (($salesCount - $prevSalesCount) / $prevSalesCount) * 100 : 0;
        $itemsChange = $prevItemsSold > 0 ? (($itemsSold - $prevItemsSold) / $prevItemsSold) * 100 : 0;
        $avgChange = $prevAvgTransaction > 0 ? (($avgTransaction - $prevAvgTransaction) / $prevAvgTransaction) * 100 : 0;

        $salesPeriodLabel = DB::raw('DATE(sales_receipts.sold_at) as day');
        if ($this->trend_granularity === 'week') {
            $salesPeriodLabel = DB::raw('CONCAT(YEAR(sales_receipts.sold_at), "-W", LPAD(WEEK(sales_receipts.sold_at, 1), 2, "0")) as day');
        } elseif ($this->trend_granularity === 'month') {
            $salesPeriodLabel = DB::raw('DATE_FORMAT(sales_receipts.sold_at, "%Y-%m") as day');
        }

        $salesByDayRaw = (clone $salesItemsBase)
            ->groupBy(DB::raw('day'))
            ->get([
                $salesPeriodLabel,
                DB::raw('SUM(sales_items.line_total) as sales_total'),
            ]);

        $prevSalesByDayRaw = SalesItem::query()
            ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
            ->join('products', 'products.id', '=', 'sales_items.product_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('sales_receipts.branch_id', $this->branch_id))
            ->whereNull('sales_receipts.voided_at')
            ->whereBetween('sales_receipts.sold_at', [$prevFrom, $prevTo])
            ->when($this->category_id > 0, fn ($q) => $q->where('products.category_id', $this->category_id))
            ->groupBy(DB::raw('day'))
            ->get([
                $salesPeriodLabel,
                DB::raw('SUM(sales_items.line_total) as sales_total'),
            ]);

        $salesByDay = [];
        $daysCount = $from->diffInDays($to) + 1;
        for ($i = 0; $i < $daysCount; $i++) {
            $currentDay = $from->copy()->addDays($i)->toDateString();
            $prevDay = $prevFrom->copy()->addDays($i)->toDateString();

            // Handle labels based on granularity
            $label = Carbon::parse($currentDay)->format('M d');
            $cKey = $currentDay;
            $pKey = $prevDay;

            if ($this->trend_granularity === 'week') {
                $cKey = $from->copy()->addDays($i)->format('Y-\WW');
                $pKey = $prevFrom->copy()->addDays($i)->format('Y-\WW');
                $label = "Week " . $from->copy()->addDays($i)->format('W');
            } elseif ($this->trend_granularity === 'month') {
                $cKey = $from->copy()->addDays($i)->format('Y-m');
                $pKey = $prevFrom->copy()->addDays($i)->format('Y-m');
                $label = $from->copy()->addDays($i)->format('M Y');
            }

            $salesByDay[] = [
                'label' => $label,
                'total' => $salesByDayRaw->firstWhere('day', $cKey)->sales_total ?? 0,
                'prev_total' => $prevSalesByDayRaw->firstWhere('day', $pKey)->sales_total ?? 0,
            ];
        }

        // Distinct entries for weekly/monthly
        if ($this->trend_granularity !== 'day') {
            $salesByDay = collect($salesByDay)->unique('label')->values()->all();
        }

        $topProducts = (clone $salesItemsBase)
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->groupBy('sales_items.product_id', 'products.name', 'categories.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get([
                'products.name as product_name',
                'categories.name as category_name',
                DB::raw('SUM(sales_items.quantity) as units_sold'),
                DB::raw('SUM(sales_items.line_total) as revenue'),
            ]);

        return view('livewire.reports-index', [
            'branches' => $branches,
            'categories' => $categories,
            'salesCount' => $salesCount,
            'salesTotal' => $salesTotal,
            'itemsSold' => $itemsSold,
            'avgTransaction' => $avgTransaction,
            'salesChange' => $salesChange,
            'countChange' => $countChange,
            'itemsChange' => $itemsChange,
            'avgChange' => $avgChange,
            'salesByDay' => $salesByDay,
            'topProducts' => $topProducts,
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
