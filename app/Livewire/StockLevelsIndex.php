<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockMovement;
use App\Support\ActivityLogger;
use Livewire\Component;
use Livewire\WithPagination;

class StockLevelsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public int $branch_id = 0;
    public string $status_filter = 'all';
    public string $stock_filter = 'all';
    public string $source_filter = 'all'; // all, opening_stock, stock_in

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

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingStockFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSourceFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $this->syncAuthContext();

        $query = ProductStock::query()
            ->with(['product', 'branch'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->branch_id > 0 && $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->status_filter === 'active', fn ($q) => $q->whereHas('product', fn ($q) => $q->where('status', 'active')))
            ->when($this->status_filter === 'inactive', fn ($q) => $q->whereHas('product', fn ($q) => $q->where('status', 'inactive')))
            ->when($this->stock_filter === 'low', fn ($q) => $q->whereColumn('current_stock', '<=', 'minimum_stock')->where('minimum_stock', '>', 0))
            ->when($this->stock_filter === 'out', fn ($q) => $q->where('current_stock', '<=', 0))
            ->when($this->stock_filter === 'available', fn ($q) => $q->where('current_stock', '>', 0))
            ->when($this->source_filter === 'opening_stock', function ($q) {
                // Products that have stock movements from opening stock adjustments
                $q->whereHas('product.stockMovements', function ($q) {
                    $q->where('notes', 'Opening stock adjustment')
                      ->where('branch_id', $this->branch_id);
                });
            })
            ->when($this->source_filter === 'stock_in', function ($q) {
                // Products that have stock from stock in receipts (not opening stock)
                $q->whereHas('product.stockMovements', function ($q) {
                    $q->whereNotNull('stock_in_receipt_id')
                      ->where('branch_id', $this->branch_id);
                });
            })
            ->when($this->search, function ($q) {
                $q->whereHas('product', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('id', 'desc');

        $stocks = $query->paginate(20);

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.stock-levels-index', [
            'stocks' => $stocks,
            'branches' => $branches,
        ]);
    }
}
