<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\BulkType;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ProductsIndex extends Component
{
    public int $branch_id = 0;
    public string $search = '';
    public string $name = '';
    public ?string $description = null;
    public ?int $category_id = null;
    public string $selling_price = '0.00';
    public bool $bulk_enabled = false;
    public ?int $bulk_type_id = null;
    public string $status = 'active';
    public ?int $editingId = null;

    public bool $show_edit_modal = false;

    public bool $show_delete_modal = false;
    public int $pending_delete_id = 0;
    public string $pending_delete_name = '';

    public bool $isSuperAdmin = false;

    public int $auth_user_id = 0;

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer', 'min:1', Rule::exists('categories', 'id')->where('branch_id', $this->branch_id)],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'bulk_enabled' => ['boolean'],
            'bulk_type_id' => ['nullable', 'integer', 'min:1', Rule::exists('bulk_types', 'id')->where('branch_id', $this->branch_id)],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }

    public function mount(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->auth_user_id = (int) ($user?->id ?? 0);

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
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
            $this->resetForm();
        }

        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        }
    }

    public function save(): void
    {
        $this->syncAuthContext();

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) (auth()->user()?->branch_id ?? 0);
        }

        $data = $this->validate();

        $data['branch_id'] = (int) $this->branch_id;

        if (! $data['bulk_enabled']) {
            $data['bulk_type_id'] = null;
        }

        if ($this->editingId) {
            Product::query()->whereKey($this->editingId)->update($data);
        } else {
            $product = Product::query()->create($data);
            $this->ensureProductStockForBranch((int) $product->id, (int) $product->branch_id);
        }

        if ($this->show_edit_modal) {
            $this->closeEditModal();
            return;
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $this->syncAuthContext();

        $product = Product::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        if ($this->isSuperAdmin) {
            $this->branch_id = (int) ($product->branch_id ?? $this->branch_id);
        }
        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->category_id = $product->category_id ? (int) $product->category_id : null;
        $this->selling_price = (string) $product->selling_price;
        $this->bulk_enabled = (bool) $product->bulk_enabled;
        $this->bulk_type_id = $product->bulk_type_id ? (int) $product->bulk_type_id : null;
        $this->status = $product->status ?? 'active';
    }

    public function openEditModal(int $id): void
    {
        $this->edit($id);
        $this->show_edit_modal = true;
    }

    public function closeEditModal(): void
    {
        $this->show_edit_modal = false;
        $this->resetForm();
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function updatedBulkEnabled(bool $value): void
    {
        if (! $value) {
            $this->bulk_type_id = null;
            return;
        }

        if ($this->bulk_type_id) {
            return;
        }

        $this->bulk_type_id = BulkType::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->value('id');

        if ($this->bulk_type_id) {
            $this->bulk_type_id = (int) $this->bulk_type_id;
        }
    }

    public function updatedBranchId(): void
    {
        if (! $this->isSuperAdmin || $this->editingId) {
            return;
        }

        $this->category_id = null;
        $this->bulk_enabled = false;
        $this->bulk_type_id = null;
    }

    public function delete(int $id): void
    {
        $this->syncAuthContext();

        $product = Product::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        ProductStock::query()->where('product_id', (int) $product->id)->delete();
        $product->delete();
        $this->resetForm();
    }

    public function openDeleteModal(int $id): void
    {
        $this->syncAuthContext();

        $product = Product::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        $this->pending_delete_id = (int) $product->id;
        $this->pending_delete_name = (string) $product->name;
        $this->show_delete_modal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->show_delete_modal = false;
        $this->pending_delete_id = 0;
        $this->pending_delete_name = '';
    }

    public function confirmDelete(): void
    {
        $id = (int) $this->pending_delete_id;
        $this->closeDeleteModal();

        if ($id > 0) {
            $this->delete($id);
        }
    }

    private function resetForm(): void
    {
        $currentBranchId = (int) $this->branch_id;

        $this->reset([
            'search',
            'name',
            'description',
            'category_id',
            'selling_price',
            'bulk_enabled',
            'bulk_type_id',
            'status',
            'editingId',
        ]);

        $this->selling_price = '0.00';
        $this->bulk_enabled = false;
        $this->bulk_type_id = null;
        $this->status = 'active';

        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        } else {
            $this->branch_id = $currentBranchId > 0
                ? $currentBranchId
                : (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        }
    }

    private function ensureProductStockForBranch(int $productId, int $branchId): void
    {
        if ($branchId <= 0) {
            return;
        }

        ProductStock::query()->firstOrCreate(
            ['branch_id' => $branchId, 'product_id' => $productId],
            ['current_stock' => 0, 'minimum_stock' => 0, 'cost_price' => null]
        );
    }

    public function render()
    {
        $this->syncAuthContext();

        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();

        $categories = Category::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->get();

        $bulkTypes = BulkType::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->get();

        $products = Product::query()
            ->with(['category', 'bulkType', 'branch'])
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->orderBy('name')
            ->get();

        return view('livewire.products-index', [
            'products' => $products,
            'categories' => $categories,
            'bulkTypes' => $bulkTypes,
            'branches' => $branches,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }
}
