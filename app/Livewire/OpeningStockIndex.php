<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockInItem;
use App\Models\StockMovement;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class OpeningStockIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public int $branch_id = 0;
    public string $status_filter = 'active';

    public bool $isSuperAdmin = false;
    public int $auth_user_id = 0;

    // Edit modal properties
    public bool $show_edit_modal = false;
    public int $editing_product_id = 0;
    public string $editing_product_name = '';
    public int $opening_quantity = 0;
    public ?string $opening_cost_price = null;
    public ?string $opening_expiry_date = null;

    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        return [
            'opening_quantity' => ['required', 'integer', 'min:0'],
            'opening_cost_price' => ['nullable', 'numeric', 'min:0'],
            'opening_expiry_date' => ['nullable', 'date'],
        ];
    }

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

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function viewOpeningStock(int $productId): void
    {
        $this->syncAuthContext();

        $product = Product::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->findOrFail($productId);

        $stock = ProductStock::query()
            ->where('product_id', $product->id)
            ->where('branch_id', $this->branch_id)
            ->first();

        $this->editing_product_id = $product->id;
        $this->editing_product_name = $product->name;
        $this->opening_quantity = $stock ? (int) $stock->current_stock : 0;
        $this->opening_cost_price = $stock && $stock->cost_price !== null ? (string) $stock->cost_price : null;
        $this->opening_expiry_date = null; // Not pre-filled - opening stock is independent

        $this->show_edit_modal = true;
    }

    public function closeViewModal(): void
    {
        $this->show_edit_modal = false;
        $this->resetViewForm();
    }

    protected function resetViewForm(): void
    {
        $this->editing_product_id = 0;
        $this->editing_product_name = '';
        $this->opening_quantity = 0;
        $this->opening_cost_price = null;
        $this->opening_expiry_date = null;
    }

    public function render()
    {
        $this->syncAuthContext();

        $query = Product::query()
            ->with(['category', 'stocks'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->branch_id > 0 && $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->status_filter === 'active', fn ($q) => $q->where('status', 'active'))
            ->when($this->status_filter === 'inactive', fn ($q) => $q->where('status', 'inactive'))
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy('name', 'asc');

        $products = $query->paginate(20);

        // Get opening stock data from stock_in_items (initial batches)
        $productIds = $products->pluck('id')->toArray();
        $openingStockData = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_items.stock_in_receipt_id', '=', 'stock_in_receipts.id')
            ->whereIn('stock_in_items.product_id', $productIds)
            ->where('stock_in_receipts.notes', 'OPENING STOCK')
            ->whereNull('stock_in_receipts.voided_at')
            ->when($this->branch_id > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $this->branch_id))
            ->select('stock_in_items.product_id', DB::raw('SUM(stock_in_items.quantity) as total_opening'), DB::raw('MAX(stock_in_items.cost_price) as last_cost'))
            ->groupBy('stock_in_items.product_id')
            ->get()
            ->keyBy('product_id');

        // Attach opening data to products
        foreach ($products as $product) {
            $data = $openingStockData->get($product->id);
            $product->actual_opening_qty = $data ? $data->total_opening : 0;
            $product->actual_opening_cost = $data ? $data->last_cost : null;
        }

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.opening-stock-index', [
            'products' => $products,
            'branches' => $branches,
        ]);
    }
}
