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

        // Get opening stock costs (first stock movement with "Opening stock" note)
        $openingCosts = DB::table('stock_movements')
            ->whereIn('product_id', $productIds)
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->where('notes', 'like', '%Opening stock%')
            ->get(['product_id', 'unit_cost'])
            ->groupBy('product_id')
            ->map(fn ($items) => $items->first()->unit_cost)
            ->toArray();

        // Get stock-in weighted average costs
        $stockInItems = StockInItem::query()
            ->whereIn('product_id', $productIds)
            ->whereHas('receipt', function ($query) {
                $query->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
                    ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id));
            })
            ->where('remaining_quantity', '>', 0)
            ->get(['product_id', 'remaining_quantity', 'cost_price']);

        $stockInCosts = [];
        foreach ($stockInItems->groupBy('product_id') as $productId => $items) {
            $totalQty = $items->sum('remaining_quantity');
            $totalCost = $items->sum(fn ($item) => $item->remaining_quantity * $item->cost_price);
            $stockInCosts[$productId] = $totalQty > 0 ? $totalCost / $totalQty : null;
        }

        // Attach cost data to products
        foreach ($products as $product) {
            $product->opening_cost_price = $openingCosts[$product->id] ?? null;
            $product->stock_in_cost_price = $stockInCosts[$product->id] ?? null;
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
        $query = ProductStock::query()
            ->join('products', 'product_stock.product_id', '=', 'products.id')
            ->where('products.status', 'active')
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('product_stock.branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('product_stock.branch_id', $this->branch_id));

        $totalQty = (clone $query)->sum('current_stock');
        $totalValue = (clone $query)
            ->selectRaw('SUM(current_stock * COALESCE(cost_price, 0)) as total_value')
            ->value('total_value') ?? 0;

        return [
            'total_products' => $query->count(),
            'total_quantity' => (int) $totalQty,
            'total_value' => (float) $totalValue,
        ];
    }
}
