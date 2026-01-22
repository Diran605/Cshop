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
                DB::raw('SUM(sales_items.quantity) as items_sold'),
            ])
            ->first();

        $salesCount = (int) ($summaryRow?->sales_count ?? 0);
        $salesTotal = (float) ($summaryRow?->sales_total ?? 0);
        $cogsTotal = (float) ($summaryRow?->cogs_total ?? 0);
        $profitTotal = (float) ($summaryRow?->profit_total ?? 0);
        $itemsSold = (int) ($summaryRow?->items_sold ?? 0);
        $avgTransaction = $salesCount > 0 ? ($salesTotal / $salesCount) : 0.0;

        $salesByDay = (clone $salesItemsBase)
            ->groupBy(DB::raw('DATE(sales_receipts.sold_at)'))
            ->orderBy(DB::raw('DATE(sales_receipts.sold_at)'))
            ->get([
                DB::raw('DATE(sales_receipts.sold_at) as day'),
                DB::raw('COUNT(DISTINCT sales_items.sales_receipt_id) as sales_count'),
                DB::raw('SUM(sales_items.line_total) as sales_total'),
                DB::raw('SUM(sales_items.line_cost) as cogs_total'),
                DB::raw('SUM(sales_items.line_profit) as profit_total'),
            ]);

        $stockInByDay = StockInReceipt::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->whereNull('voided_at')
            ->whereBetween('received_at', [$from, $to])
            ->groupBy(DB::raw('DATE(received_at)'))
            ->orderBy(DB::raw('DATE(received_at)'))
            ->get([
                DB::raw('DATE(received_at) as day'),
                DB::raw('SUM(total_quantity) as stock_in_qty'),
                DB::raw('SUM(total_cost) as stock_in_cost'),
            ]);

        $salesQtyByDay = (clone $salesItemsBase)
            ->groupBy(DB::raw('DATE(sales_receipts.sold_at)'))
            ->orderBy(DB::raw('DATE(sales_receipts.sold_at)'))
            ->get([
                DB::raw('DATE(sales_receipts.sold_at) as day'),
                DB::raw('SUM(sales_items.quantity) as sold_qty'),
            ]);

        $movementByDay = [];
        foreach ($stockInByDay as $row) {
            $movementByDay[(string) $row->day] = [
                'day' => (string) $row->day,
                'stock_in_qty' => (int) ($row->stock_in_qty ?? 0),
                'sold_qty' => 0,
            ];
        }
        foreach ($salesQtyByDay as $row) {
            $day = (string) $row->day;
            if (! isset($movementByDay[$day])) {
                $movementByDay[$day] = [
                    'day' => $day,
                    'stock_in_qty' => 0,
                    'sold_qty' => (int) ($row->sold_qty ?? 0),
                ];
            } else {
                $movementByDay[$day]['sold_qty'] = (int) ($row->sold_qty ?? 0);
            }
        }
        ksort($movementByDay);
        $movementByDay = array_values($movementByDay);

        $topProducts = (clone $salesItemsBase)
            ->groupBy('sales_items.product_id', 'products.name')
            ->orderByDesc('qty_sold')
            ->limit(10)
            ->get([
                'products.name as product_name',
                DB::raw('SUM(sales_items.quantity) as qty_sold'),
                DB::raw('SUM(sales_items.line_total) as amount_sold'),
                DB::raw('SUM(sales_items.line_profit) as profit_total'),
            ]);

        if (trim($this->search) !== '') {
            $term = strtolower(trim($this->search));
            $topProducts = $topProducts->filter(fn ($r) => str_contains(strtolower((string) $r->product_name), $term));
        }

        $inventoryQuery = ProductStock::query()
            ->with(['product'])
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where('products.name', 'like', $term);
            })
            ->orderBy('products.name')
            ->select('product_stocks.*');

        if ($this->low_stock_only) {
            $inventoryQuery->whereColumn('product_stocks.current_stock', '<=', 'product_stocks.minimum_stock');
        }

        $inventory = $inventoryQuery->get();

        $productMovement = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->join('products', 'products.id', '=', 'stock_in_items.product_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $this->branch_id))
            ->whereNull('stock_in_receipts.voided_at')
            ->whereBetween('stock_in_receipts.received_at', [$from, $to])
            ->groupBy('stock_in_items.product_id', 'products.name')
            ->select([
                'stock_in_items.product_id',
                'products.name as product_name',
                DB::raw('SUM(stock_in_items.quantity) as stock_in_qty'),
            ])
            ->get()
            ->keyBy('product_id');

        $soldByProduct = (clone $salesItemsBase)
            ->groupBy('sales_items.product_id', 'products.name')
            ->select([
                'sales_items.product_id',
                'products.name as product_name',
                DB::raw('SUM(sales_items.quantity) as sold_qty'),
            ])
            ->get()
            ->keyBy('product_id');

        $movementRows = [];
        $allProductIds = array_unique(array_merge($productMovement->keys()->all(), $soldByProduct->keys()->all()));
        foreach ($allProductIds as $productId) {
            $inRow = $productMovement->get($productId);
            $soldRow = $soldByProduct->get($productId);

            $movementRows[] = [
                'product_name' => (string) ($inRow?->product_name ?? $soldRow?->product_name ?? '-'),
                'stock_in_qty' => (int) ($inRow?->stock_in_qty ?? 0),
                'sold_qty' => (int) ($soldRow?->sold_qty ?? 0),
            ];
        }

        if (trim($this->search) !== '') {
            $term = strtolower(trim($this->search));
            $movementRows = array_values(array_filter($movementRows, fn ($r) => str_contains(strtolower((string) $r['product_name']), $term)));
        }

        usort($movementRows, fn ($a, $b) => strcmp($a['product_name'], $b['product_name']));

        return view('livewire.reports-index', [
            'branches' => $branches,
            'categories' => $categories,
            'productsForFilter' => $productsForFilter,
            'salesCount' => $salesCount,
            'salesTotal' => $salesTotal,
            'cogsTotal' => $cogsTotal,
            'profitTotal' => $profitTotal,
            'itemsSold' => $itemsSold,
            'avgTransaction' => $avgTransaction,
            'salesByDay' => $salesByDay,
            'movementByDay' => $movementByDay,
            'topProducts' => $topProducts,
            'inventory' => $inventory,
            'movementRows' => $movementRows,
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
