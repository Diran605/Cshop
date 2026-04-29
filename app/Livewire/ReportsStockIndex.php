<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesItem;
use App\Models\StockInItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ReportsStockIndex extends Component
{
    public int $branch_id = 0;
    public string $date_from;
    public string $date_to;

    public int $category_id = 0;
    public string $search = '';

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
            $this->search = '';
        }

        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
    }

    #[Computed]
    public function stockMetrics()
    {
        $branchId = $this->isSuperAdmin && $this->branch_id > 0 ? $this->branch_id : ($this->isSuperAdmin ? 0 : auth()->user()->branch_id);

        return StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_items.stock_in_receipt_id', '=', 'stock_in_receipts.id')
            ->join('products', 'products.id', '=', 'stock_in_items.product_id')
            ->whereNull('stock_in_receipts.voided_at')
            ->where('stock_in_items.remaining_quantity', '>', 0)
            ->when($branchId > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $branchId))
            ->when($this->category_id > 0, fn ($q) => $q->where('products.category_id', $this->category_id))
            ->select([
                DB::raw('COUNT(DISTINCT stock_in_items.product_id) as total_items'),
                DB::raw('SUM(stock_in_items.remaining_quantity) as total_qty'),
                DB::raw('SUM(stock_in_items.remaining_quantity * COALESCE(stock_in_items.cost_price, 0)) as total_value'),
            ])
            ->first();
    }

    #[Computed]
    public function lowStockCount()
    {
        $branchId = $this->isSuperAdmin && $this->branch_id > 0 ? $this->branch_id : ($this->isSuperAdmin ? 0 : auth()->user()->branch_id);

        return ProductStock::query()
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->when($branchId > 0, fn ($q) => $q->where('product_stocks.branch_id', $branchId))
            ->when($this->category_id > 0, fn ($q) => $q->where('products.category_id', $this->category_id))
            ->whereColumn('product_stocks.current_stock', '<=', 'product_stocks.minimum_stock')
            ->where('product_stocks.current_stock', '>', 0)
            ->count();
    }

    #[Computed]
    public function outOfStockCount()
    {
        $branchId = $this->isSuperAdmin && $this->branch_id > 0 ? $this->branch_id : ($this->isSuperAdmin ? 0 : auth()->user()->branch_id);

        return ProductStock::query()
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->when($branchId > 0, fn ($q) => $q->where('product_stocks.branch_id', $branchId))
            ->when($this->category_id > 0, fn ($q) => $q->where('products.category_id', $this->category_id))
            ->where('product_stocks.current_stock', '<=', 0)
            ->count();
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

        $daysCount = $from->diffInDays($to) + 1;
        $prevTo = $from->copy()->subSecond();
        $prevFrom = $prevTo->copy()->subDays($daysCount - 1)->startOfDay();

        $categories = \App\Models\Category::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->get();

        // Metrics (Current Snapshot)
        $stockMetrics = $this->stockMetrics;
        $lowStockCount = $this->lowStockCount;
        $outOfStockCount = $this->outOfStockCount;

        // Stock In / Sold Comparison
        $currentStockIn = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $this->branch_id))
            ->whereNull('stock_in_receipts.voided_at')
            ->whereBetween('stock_in_receipts.received_at', [$from, $to])
            ->sum('stock_in_items.quantity');

        $prevStockIn = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $this->branch_id))
            ->whereNull('stock_in_receipts.voided_at')
            ->whereBetween('stock_in_receipts.received_at', [$prevFrom, $prevTo])
            ->sum('stock_in_items.quantity');

        $currentSold = SalesItem::query()
            ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('sales_receipts.branch_id', $this->branch_id))
            ->whereNull('sales_receipts.voided_at')
            ->whereBetween('sales_receipts.sold_at', [$from, $to])
            ->sum('sales_items.quantity');

        $prevSold = SalesItem::query()
            ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('sales_receipts.branch_id', $this->branch_id))
            ->whereNull('sales_receipts.voided_at')
            ->whereBetween('sales_receipts.sold_at', [$prevFrom, $prevTo])
            ->sum('sales_items.quantity');

        $stockInChange = $prevStockIn > 0 ? (($currentStockIn - $prevStockIn) / $prevStockIn) * 100 : 0;
        $soldChange = $prevSold > 0 ? (($currentSold - $prevSold) / $prevSold) * 100 : 0;

        // Category Breakdown (for Chart)
        $categoryStock = ProductStock::query()
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->join('categories', 'categories.id', '=', 'products.category_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('product_stocks.branch_id', $this->branch_id))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('stock_value')
            ->limit(10)
            ->get([
                'categories.name',
                DB::raw('SUM(product_stocks.current_stock * product_stocks.cost_price) as stock_value'),
                DB::raw('SUM(product_stocks.current_stock) as total_qty'),
            ]);

        // Stock Movement (Trend)
        $stockInByDay = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $this->branch_id))
            ->whereNull('stock_in_receipts.voided_at')
            ->whereBetween('stock_in_receipts.received_at', [$from, $to])
            ->groupBy(DB::raw('DATE(stock_in_receipts.received_at)'))
            ->get([
                DB::raw('DATE(stock_in_receipts.received_at) as day'),
                DB::raw('SUM(stock_in_items.quantity) as qty'),
            ]);

        $soldByDay = SalesItem::query()
            ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('sales_receipts.branch_id', $this->branch_id))
            ->whereNull('sales_receipts.voided_at')
            ->whereBetween('sales_receipts.sold_at', [$from, $to])
            ->groupBy(DB::raw('DATE(sales_receipts.sold_at)'))
            ->get([
                DB::raw('DATE(sales_receipts.sold_at) as day'),
                DB::raw('SUM(sales_items.quantity) as qty'),
            ]);

        // Combine trend data
        $trendData = [];
        $allDays = $stockInByDay->pluck('day')->merge($soldByDay->pluck('day'))->unique()->sort();
        
        // Previous period trend data
        $prevStockInByDay = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $this->branch_id))
            ->whereNull('stock_in_receipts.voided_at')
            ->whereBetween('stock_in_receipts.received_at', [$prevFrom, $prevTo])
            ->groupBy(DB::raw('DATE(stock_in_receipts.received_at)'))
            ->get([
                DB::raw('DATE(stock_in_receipts.received_at) as day'),
                DB::raw('SUM(stock_in_items.quantity) as qty'),
            ]);

        $prevSoldByDay = SalesItem::query()
            ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('sales_receipts.branch_id', $this->branch_id))
            ->whereNull('sales_receipts.voided_at')
            ->whereBetween('sales_receipts.sold_at', [$prevFrom, $prevTo])
            ->groupBy(DB::raw('DATE(sales_receipts.sold_at)'))
            ->get([
                DB::raw('DATE(sales_receipts.sold_at) as day'),
                DB::raw('SUM(sales_items.quantity) as qty'),
            ]);

        // We'll align by day index (1 to daysCount) for comparison
        for ($i = 0; $i < $daysCount; $i++) {
            $currentDay = $from->copy()->addDays($i)->toDateString();
            $prevDay = $prevFrom->copy()->addDays($i)->toDateString();

            $trendData[] = [
                'day' => Carbon::parse($currentDay)->format('M d'),
                'in' => $stockInByDay->firstWhere('day', $currentDay)->qty ?? 0,
                'out' => $soldByDay->firstWhere('day', $currentDay)->qty ?? 0,
                'prev_in' => $prevStockInByDay->firstWhere('day', $prevDay)->qty ?? 0,
                'prev_out' => $prevSoldByDay->firstWhere('day', $prevDay)->qty ?? 0,
            ];
        }

        // Attention List (Low Stock Items)
        $attentionList = ProductStock::query()
            ->with(['product.unitType', 'product.category'])
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('product_stocks.branch_id', $this->branch_id))
            ->when($this->category_id > 0, fn ($q) => $q->where('products.category_id', $this->category_id))
            ->whereColumn('product_stocks.current_stock', '<=', 'product_stocks.minimum_stock')
            ->orderBy('product_stocks.current_stock')
            ->limit(10)
            ->get(['product_stocks.*', 'products.name as product_name']);

        return view('livewire.reports-stock-index', [
            'branches' => $branches,
            'categories' => $categories,
            'metrics' => $stockMetrics,
            'currentStockIn' => $currentStockIn,
            'stockInChange' => $stockInChange,
            'currentSold' => $currentSold,
            'soldChange' => $soldChange,
            'categoryStock' => $categoryStock,
            'trendData' => $trendData,
            'attentionList' => $attentionList,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }
}
