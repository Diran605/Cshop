<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockInItem;
use App\Models\StockInReceipt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StockInIndex extends Component
{
    public int $branch_id = 0;
    public int $product_id = 0;
    public int $quantity = 1;
    public ?string $cost_price = null;

    public ?string $notes = null;
    public int $selected_receipt_id = 0;

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'min:1'],
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function mount(): void
    {
        $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        $this->product_id = (int) (Product::query()->orderBy('name')->value('id') ?? 0);
        $this->quantity = 1;
        $this->cost_price = null;
        $this->notes = null;
        $this->selected_receipt_id = 0;
    }

    public function save(): void
    {
        $data = $this->validate();

        $receiptId = DB::transaction(function () use ($data) {
            $stock = ProductStock::query()->firstOrCreate(
                ['branch_id' => (int) $data['branch_id'], 'product_id' => (int) $data['product_id']],
                ['current_stock' => 0, 'minimum_stock' => 0, 'cost_price' => null]
            );

            $stock->current_stock = (int) $stock->current_stock + (int) $data['quantity'];

            if ($data['cost_price'] !== null && $data['cost_price'] !== '') {
                $stock->cost_price = $data['cost_price'];
            }

            $stock->save();

            $receipt = StockInReceipt::query()->create([
                'receipt_no' => 'SI-' . strtoupper(Str::random(10)),
                'branch_id' => (int) $data['branch_id'],
                'user_id' => auth()->id(),
                'received_at' => now(),
                'notes' => $data['notes'] ?? null,
                'total_quantity' => (int) $data['quantity'],
                'total_cost' => ($data['cost_price'] !== null && $data['cost_price'] !== '')
                    ? (string) ((float) $data['cost_price'] * (int) $data['quantity'])
                    : null,
            ]);

            StockInItem::query()->create([
                'stock_in_receipt_id' => $receipt->id,
                'product_id' => (int) $data['product_id'],
                'quantity' => (int) $data['quantity'],
                'cost_price' => ($data['cost_price'] !== null && $data['cost_price'] !== '') ? $data['cost_price'] : null,
                'line_total' => ($data['cost_price'] !== null && $data['cost_price'] !== '')
                    ? (string) ((float) $data['cost_price'] * (int) $data['quantity'])
                    : null,
            ]);

            return (int) $receipt->id;
        });

        $this->reset(['quantity', 'cost_price', 'notes']);
        $this->quantity = 1;
        $this->cost_price = null;
        $this->notes = null;
        $this->selected_receipt_id = (int) $receiptId;

        session()->flash('status', 'Stock updated successfully.');
    }

    public function selectReceipt(int $id): void
    {
        $this->selected_receipt_id = $id;
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

        $receipts = StockInReceipt::query()
            ->with(['branch', 'user'])
            ->orderByDesc('received_at')
            ->limit(15)
            ->get();

        $selectedReceipt = null;
        if ($this->selected_receipt_id > 0) {
            $selectedReceipt = StockInReceipt::query()
                ->with(['branch', 'user', 'items.product'])
                ->find($this->selected_receipt_id);
        }

        return view('livewire.stock-in-index', [
            'branches' => $branches,
            'products' => $products,
            'stocks' => $stocks,
            'receipts' => $receipts,
            'selectedReceipt' => $selectedReceipt,
        ]);
    }
}
