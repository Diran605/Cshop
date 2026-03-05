<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
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

    public function editOpeningStock(int $productId): void
    {
        $this->syncAuthContext();

        $product = Product::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->findOrFail($productId);

        $stock = ProductStock::query()
            ->where('product_id', $product->id)
            ->where('branch_id', $this->branch_id)
            ->first();

        // Get latest expiry date from stock in items
        $latestStockItem = StockInItem::query()
            ->where('product_id', $product->id)
            ->whereHas('receipt', function ($query) {
                $query->where('branch_id', $this->branch_id);
            })
            ->whereNotNull('expiry_date')
            ->orderBy('created_at', 'desc')
            ->first();

        $this->editing_product_id = $product->id;
        $this->editing_product_name = $product->name;
        $this->opening_quantity = $stock ? (int) $stock->current_stock : 0;
        $this->opening_cost_price = $stock && $stock->cost_price !== null ? (string) $stock->cost_price : null;
        $this->opening_expiry_date = $latestStockItem ? $latestStockItem->expiry_date->toDateString() : null;

        $this->show_edit_modal = true;
    }

    public function saveOpeningStock(): void
    {
        $this->validate();

        $this->syncAuthContext();

        $product = Product::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->findOrFail($this->editing_product_id);

        DB::transaction(function () use ($product) {
            $stock = ProductStock::query()
                ->where('product_id', $product->id)
                ->where('branch_id', $this->branch_id)
                ->first();

            $oldQuantity = $stock ? (int) $stock->current_stock : 0;
            $newQuantity = (int) $this->opening_quantity;
            $quantityDiff = $newQuantity - $oldQuantity;

            // Update or create product stock
            if ($stock) {
                $stock->current_stock = $newQuantity;
                $stock->cost_price = ($this->opening_cost_price !== null && $this->opening_cost_price !== '')
                    ? number_format((float) $this->opening_cost_price, 2, '.', '')
                    : null;
                $stock->save();
            } else {
                $stock = ProductStock::query()->create([
                    'branch_id' => $this->branch_id,
                    'product_id' => $product->id,
                    'current_stock' => $newQuantity,
                    'minimum_stock' => 0,
                    'cost_price' => ($this->opening_cost_price !== null && $this->opening_cost_price !== '')
                        ? number_format((float) $this->opening_cost_price, 2, '.', '')
                        : null,
                ]);
            }

            // Create stock movement if quantity changed
            if ($quantityDiff !== 0) {
                StockMovement::query()->create([
                    'branch_id' => $this->branch_id,
                    'product_id' => $product->id,
                    'user_id' => $this->auth_user_id,
                    'movement_type' => $quantityDiff > 0 ? 'IN' : 'OUT',
                    'quantity' => abs($quantityDiff),
                    'before_stock' => $oldQuantity,
                    'after_stock' => $newQuantity,
                    'unit_cost' => ($this->opening_cost_price !== null && $this->opening_cost_price !== '')
                        ? number_format((float) $this->opening_cost_price, 2, '.', '')
                        : null,
                    'unit_price' => $product->selling_price,
                    'moved_at' => Carbon::now(),
                    'notes' => 'Opening stock adjustment',
                ]);
            }

            ActivityLogger::log(
                'opening_stock.updated',
                ['type' => Product::class, 'id' => $product->id],
                "Updated opening stock for {$product->name}: {$oldQuantity} → {$newQuantity}",
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'cost_price' => $this->opening_cost_price,
                    'expiry_date' => $this->opening_expiry_date,
                ],
                $this->branch_id
            );
        });

        $this->show_edit_modal = false;
        $this->resetEditForm();
        session()->flash('status', 'Opening stock updated successfully.');
    }

    public function closeEditModal(): void
    {
        $this->show_edit_modal = false;
        $this->resetEditForm();
    }

    protected function resetEditForm(): void
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
