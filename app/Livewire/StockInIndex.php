<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StockInIndex extends Component
{
    public int $branch_id = 0;
    public int $product_id = 0;
    public int $quantity = 1;
    public ?string $cost_price = null;

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'min:1'],
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function mount(): void
    {
        $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        $this->product_id = (int) (Product::query()->orderBy('name')->value('id') ?? 0);
        $this->quantity = 1;
        $this->cost_price = null;
    }

    public function save(): void
    {
        $data = $this->validate();

        DB::transaction(function () use ($data) {
            $stock = ProductStock::query()->firstOrCreate(
                ['branch_id' => (int) $data['branch_id'], 'product_id' => (int) $data['product_id']],
                ['current_stock' => 0, 'minimum_stock' => 0, 'cost_price' => null]
            );

            $stock->current_stock = (int) $stock->current_stock + (int) $data['quantity'];

            if ($data['cost_price'] !== null && $data['cost_price'] !== '') {
                $stock->cost_price = $data['cost_price'];
            }

            $stock->save();
        });

        $this->reset(['quantity', 'cost_price']);
        $this->quantity = 1;
        $this->cost_price = null;
        session()->flash('status', 'Stock updated successfully.');
    }

    public function render()
    {
        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();
        $products = Product::query()->orderBy('name')->get();

        $stocks = ProductStock::query()
            ->with(['product'])
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->orderBy('products.name')
            ->select('product_stocks.*')
            ->get();

        return view('livewire.stock-in-index', [
            'branches' => $branches,
            'products' => $products,
            'stocks' => $stocks,
        ]);
    }
}
