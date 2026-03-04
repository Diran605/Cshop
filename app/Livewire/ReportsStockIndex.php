<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesItem;
use App\Models\StockInItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportsStockIndex extends Component
{
    public int $branch_id = 0;
    public string $date_from;
    public string $date_to;

    public bool $low_stock_only = false;
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

        $this->low_stock_only = false;
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

        $products = Product::query()
            ->with(['unitType'])
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->get();

        $from = Carbon::parse($this->date_from)->startOfDay();
        $to = Carbon::parse($this->date_to)->endOfDay();

        $stockInByDay = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $this->branch_id))
            ->whereNull('stock_in_receipts.voided_at')
            ->whereBetween('stock_in_receipts.received_at', [$from, $to])
            ->groupBy(DB::raw('DATE(stock_in_receipts.received_at)'))
            ->orderBy(DB::raw('DATE(stock_in_receipts.received_at)'))
            ->get([
                DB::raw('DATE(stock_in_receipts.received_at) as day'),
                DB::raw('SUM(stock_in_items.quantity) as stock_in_qty'),
            ]);

        $soldByDay = SalesItem::query()
            ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('sales_receipts.branch_id', $this->branch_id))
            ->whereNull('sales_receipts.voided_at')
            ->whereBetween('sales_receipts.sold_at', [$from, $to])
            ->groupBy(DB::raw('DATE(sales_receipts.sold_at)'))
            ->orderBy(DB::raw('DATE(sales_receipts.sold_at)'))
            ->get([
                DB::raw('DATE(sales_receipts.sold_at) as day'),
                DB::raw('SUM(sales_items.quantity) as sold_qty'),
            ]);

        $inventoryQuery = ProductStock::query()
            ->with(['product.unitType'])
            ->when($this->branch_id > 0, fn ($q) => $q->where('product_stocks.branch_id', $this->branch_id))
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

        $salesQtyByProduct = SalesItem::query()
            ->join('sales_receipts', 'sales_receipts.id', '=', 'sales_items.sales_receipt_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('sales_receipts.branch_id', $this->branch_id))
            ->whereNull('sales_receipts.voided_at')
            ->whereBetween('sales_receipts.sold_at', [$from, $to])
            ->groupBy('sales_items.product_id')
            ->select([
                'sales_items.product_id',
                DB::raw('SUM(sales_items.quantity) as sold_qty'),
            ])
            ->get()
            ->keyBy('product_id');

        $stockInQtyByProduct = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->when($this->branch_id > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $this->branch_id))
            ->whereNull('stock_in_receipts.voided_at')
            ->whereBetween('stock_in_receipts.received_at', [$from, $to])
            ->groupBy('stock_in_items.product_id')
            ->select([
                'stock_in_items.product_id',
                DB::raw('SUM(stock_in_items.quantity) as stock_in_qty'),
            ])
            ->get()
            ->keyBy('product_id');

        $movementRows = [];
        foreach ($inventory as $row) {
            $pid = (int) $row->product_id;
            $movementRows[] = [
                'product_name' => (string) ($row->product?->name ?? '-'),
                'unit_type_name' => $row->product?->unitType?->name,
                'current_stock' => (int) ($row->current_stock ?? 0),
                'minimum_stock' => (int) ($row->minimum_stock ?? 0),
                'stock_in_qty' => (int) ($stockInQtyByProduct[$pid]->stock_in_qty ?? 0),
                'sold_qty' => (int) ($salesQtyByProduct[$pid]->sold_qty ?? 0),
            ];
        }

        usort($movementRows, fn ($a, $b) => strcmp((string) $a['product_name'], (string) $b['product_name']));

        $totalProducts = count($movementRows);
        $lowStockCount = count(array_filter($movementRows, fn ($r) => (int) $r['current_stock'] <= (int) $r['minimum_stock']));

        $fastMoving = $movementRows;
        usort($fastMoving, fn ($a, $b) => (int) $b['sold_qty'] <=> (int) $a['sold_qty']);
        $fastMoving = array_slice($fastMoving, 0, 10);

        $slowMoving = array_values(array_filter($movementRows, fn ($r) => (int) $r['sold_qty'] > 0));
        usort($slowMoving, fn ($a, $b) => (int) $a['sold_qty'] <=> (int) $b['sold_qty']);
        $slowMoving = array_slice($slowMoving, 0, 10);

        return view('livewire.reports-stock-index', [
            'branches' => $branches,
            'products' => $products,
            'inventory' => $inventory,
            'movementRows' => $movementRows,
            'totalProducts' => $totalProducts,
            'lowStockCount' => $lowStockCount,
            'stockInByDay' => $stockInByDay,
            'soldByDay' => $soldByDay,
            'fastMoving' => $fastMoving,
            'slowMoving' => $slowMoving,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }
}
