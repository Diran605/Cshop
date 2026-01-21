<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\BulkType;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductStock;
use Livewire\Component;

class ProductsIndex extends Component
{
    public string $name = '';
    public ?string $description = null;
    public ?int $category_id = null;
    public string $selling_price = '0.00';
    public bool $bulk_enabled = false;
    public ?int $bulk_type_id = null;
    public string $status = 'active';
    public ?int $editingId = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'integer', 'min:1'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'bulk_enabled' => ['boolean'],
            'bulk_type_id' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if (! $data['bulk_enabled']) {
            $data['bulk_type_id'] = null;
        }

        if ($this->editingId) {
            Product::query()->whereKey($this->editingId)->update($data);
        } else {
            $product = Product::query()->create($data);
            $this->ensureProductStocksForAllBranches($product->id);
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $product = Product::query()->findOrFail($id);
        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->category_id = $product->category_id ? (int) $product->category_id : null;
        $this->selling_price = (string) $product->selling_price;
        $this->bulk_enabled = (bool) $product->bulk_enabled;
        $this->bulk_type_id = $product->bulk_type_id ? (int) $product->bulk_type_id : null;
        $this->status = $product->status ?? 'active';
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function updatedBulkEnabled(bool $value): void
    {
        if (! $value) {
            $this->bulk_type_id = null;
        }
    }

    public function delete(int $id): void
    {
        ProductStock::query()->where('product_id', $id)->delete();
        Product::query()->findOrFail($id)->forceDelete();
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset([
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
    }

    private function ensureProductStocksForAllBranches(int $productId): void
    {
        $branches = Branch::query()->where('is_active', true)->get(['id']);

        foreach ($branches as $branch) {
            ProductStock::query()->firstOrCreate(
                ['branch_id' => $branch->id, 'product_id' => $productId],
                ['current_stock' => 0, 'minimum_stock' => 0, 'cost_price' => null]
            );
        }
    }

    public function render()
    {
        $categories = Category::query()->orderBy('name')->get();
        $bulkTypes = BulkType::query()->orderBy('name')->get();

        $products = Product::query()
            ->with(['category', 'bulkType'])
            ->orderBy('name')
            ->get();

        return view('livewire.products-index', [
            'products' => $products,
            'categories' => $categories,
            'bulkTypes' => $bulkTypes,
        ]);
    }
}
