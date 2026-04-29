<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockInItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class StockValuationIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public int $branch_id = 0;
    public string $category_filter = '';

    public bool $isSuperAdmin = false;
    public int $auth_user_id = 0;

    protected $paginationTheme = 'tailwind';

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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBranchId(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->syncAuthContext();

        $branches = $this->isSuperAdmin
            ? Branch::query()->where('is_active', true)->orderBy('name')->get()
            : collect();

        // Get categories for filter
        $categories = DB::table('categories')
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->get(['id', 'name']);

        // Get products with stock data
        $products = Product::query()
            ->with(['category', 'stock', 'branch'])
            ->where('status', 'active')
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->category_filter, fn ($q) => $q->where('category_id', $this->category_filter))
            ->orderBy('name')
            ->paginate(20);

        // Enrich products with cost price data
        $productIds = $products->pluck('id')->toArray();

        // Get batch-level valuation per product
        $batchData = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_items.stock_in_receipt_id', '=', 'stock_in_receipts.id')
            ->whereIn('stock_in_items.product_id', $productIds)
            ->whereNull('stock_in_receipts.voided_at')
            ->where('stock_in_items.remaining_quantity', '>', 0)
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('stock_in_receipts.branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $this->branch_id))
            ->select(
                'stock_in_items.product_id',
                DB::raw('SUM(stock_in_items.remaining_quantity) as total_qty'),
                DB::raw('SUM(stock_in_items.remaining_quantity * COALESCE(stock_in_items.cost_price, 0)) as total_value'),
                DB::raw('MAX(stock_in_items.cost_price) as latest_cost') // Fallback for display
            )
            ->groupBy('stock_in_items.product_id')
            ->get()
            ->keyBy('product_id');

        // Attach cost data to products
        foreach ($products as $product) {
            $data = $batchData->get($product->id);
            $product->batch_total_qty = $data ? $data->total_qty : 0;
            $product->batch_total_value = $data ? $data->total_value : 0;
            // Use the average batch cost for the "Cost Price" display if multiple batches exist
            $product->actual_cost_price = ($data && $data->total_qty > 0) ? ($data->total_value / $data->total_qty) : ($data->latest_cost ?? 0);
        }

        // Calculate stock valuation summary
        $summary = $this->calculateSummary();

        return view('livewire.stock-valuation-index', [
            'branches' => $branches,
            'categories' => $categories,
            'products' => $products,
            'summary' => $summary,
        ]);
    }

    protected function calculateSummary(): array
    {
        $branchId = $this->isSuperAdmin && $this->branch_id > 0 ? $this->branch_id : ($this->isSuperAdmin ? 0 : $this->branch_id);

        $query = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_items.stock_in_receipt_id', '=', 'stock_in_receipts.id')
            ->join('products', 'stock_in_items.product_id', '=', 'products.id')
            ->where('products.status', 'active')
            ->whereNull('stock_in_receipts.voided_at')
            ->where('stock_in_items.remaining_quantity', '>', 0)
            ->when($branchId > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $branchId));

        $totalQty = (clone $query)->sum('stock_in_items.remaining_quantity');
        $totalValue = (clone $query)
            ->selectRaw('SUM(stock_in_items.remaining_quantity * COALESCE(stock_in_items.cost_price, 0)) as total_value')
            ->value('total_value') ?? 0;

        return [
            'total_products' => (clone $query)->distinct('stock_in_items.product_id')->count('stock_in_items.product_id'),
            'total_quantity' => (int) $totalQty,
            'total_value' => (float) $totalValue,
        ];
    }
}
