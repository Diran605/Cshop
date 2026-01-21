<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockInItem;
use App\Models\StockInReceipt;
use App\Models\StockMovement;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class StockInIndex extends Component
{
    public int $branch_id = 0;
    public int $product_id = 0;

    public string $entry_mode = 'unit';
    public int $bulk_quantity = 1;

    public int $quantity = 1;
    public ?string $cost_price = null;

    public bool $isSuperAdmin = false;

    public ?string $notes = null;
    public int $selected_receipt_id = 0;

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'min:1'],
            'product_id' => ['required', 'integer', 'min:1'],
            'entry_mode' => ['required', 'string', 'in:unit,bulk'],
            'bulk_quantity' => ['nullable', 'integer', 'min:1', 'required_if:entry_mode,bulk'],
            'quantity' => ['nullable', 'integer', 'min:1', 'required_if:entry_mode,unit'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function mount(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        } else {
            $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        }

        $this->product_id = (int) (Product::query()->orderBy('name')->value('id') ?? 0);
        $this->entry_mode = 'unit';
        $this->bulk_quantity = 1;
        $this->quantity = 1;
        $this->cost_price = null;
        $this->notes = null;
        $this->selected_receipt_id = 0;
    }

    public function updatedProductId(): void
    {
        $product = Product::query()->with(['bulkType'])->find($this->product_id);
        if ($product && (bool) $product->bulk_enabled) {
            $this->entry_mode = 'bulk';
            $this->bulk_quantity = max(1, (int) $this->bulk_quantity);
            return;
        }

        $this->entry_mode = 'unit';
        $this->bulk_quantity = 1;
        $this->quantity = max(1, (int) $this->quantity);
    }

    public function save(): void
    {
        $data = $this->validate();

        $product = Product::query()->with(['bulkType'])->find((int) $data['product_id']);
        if (! $product) {
            return;
        }

        $bulkTypeId = null;
        $unitsPerBulk = null;
        $bulkQty = null;

        if ((string) $data['entry_mode'] === 'bulk') {
            if (! (bool) $product->bulk_enabled) {
                $this->addError('entry_mode', 'Bulk mode is not enabled for the selected product.');
                return;
            }

            $bulkType = $product->bulkType;
            if (! $bulkType) {
                $this->addError('entry_mode', 'Bulk type is not configured for the selected product.');
                return;
            }

            $bulkTypeId = (int) $bulkType->id;
            $unitsPerBulk = (int) $bulkType->units_per_bulk;
            $bulkQty = (int) ($data['bulk_quantity'] ?? 0);

            if ($unitsPerBulk <= 0) {
                $this->addError('entry_mode', 'Invalid units per bulk configuration.');
                return;
            }
            if ($bulkQty <= 0) {
                $this->addError('bulk_quantity', 'Bulk quantity must be at least 1.');
                return;
            }

            $data['quantity'] = $bulkQty * $unitsPerBulk;
        } else {
            $data['quantity'] = (int) $data['quantity'];
        }

        if (! $this->isSuperAdmin) {
            $data['branch_id'] = (int) (auth()->user()?->branch_id ?? 0);
            $this->branch_id = (int) $data['branch_id'];
        }

        $receiptId = DB::transaction(function () use ($data, $bulkQty, $unitsPerBulk, $bulkTypeId) {
            $stock = ProductStock::query()->firstOrCreate(
                ['branch_id' => (int) $data['branch_id'], 'product_id' => (int) $data['product_id']],
                ['current_stock' => 0, 'minimum_stock' => 0, 'cost_price' => null]
            );

            $beforeStock = (int) $stock->current_stock;
            $beforeCost = $stock->cost_price !== null ? (float) $stock->cost_price : null;

            $stock->current_stock = (int) $stock->current_stock + (int) $data['quantity'];

            $incomingCost = ($data['cost_price'] !== null && $data['cost_price'] !== '')
                ? (float) $data['cost_price']
                : $beforeCost;

            if ($incomingCost !== null) {
                $afterQty = $beforeStock + (int) $data['quantity'];
                if ($afterQty > 0) {
                    $beforeCostValue = $beforeCost !== null ? (float) $beforeCost : (float) $incomingCost;
                    $weighted = (($beforeStock * $beforeCostValue) + ((int) $data['quantity'] * $incomingCost)) / $afterQty;
                    $stock->cost_price = number_format($weighted, 2, '.', '');
                }
            }

            $stock->save();

            $afterStock = (int) $stock->current_stock;

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
                'entry_mode' => (string) $data['entry_mode'],
                'bulk_quantity' => $bulkQty,
                'units_per_bulk' => $unitsPerBulk,
                'bulk_type_id' => $bulkTypeId,
                'quantity' => (int) $data['quantity'],
                'cost_price' => ($data['cost_price'] !== null && $data['cost_price'] !== '') ? $data['cost_price'] : null,
                'line_total' => ($data['cost_price'] !== null && $data['cost_price'] !== '')
                    ? (string) ((float) $data['cost_price'] * (int) $data['quantity'])
                    : null,
            ]);

            StockMovement::query()->create([
                'branch_id' => (int) $data['branch_id'],
                'product_id' => (int) $data['product_id'],
                'user_id' => auth()->id(),
                'movement_type' => 'IN',
                'quantity' => (int) $data['quantity'],
                'before_stock' => $beforeStock,
                'after_stock' => $afterStock,
                'unit_cost' => ($data['cost_price'] !== null && $data['cost_price'] !== '') ? (string) $data['cost_price'] : null,
                'unit_price' => null,
                'stock_in_receipt_id' => (int) $receipt->id,
                'sales_receipt_id' => null,
                'moved_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            return (int) $receipt->id;
        });

        $this->reset(['quantity', 'cost_price', 'notes']);
        $this->quantity = 1;
        $this->entry_mode = 'unit';
        $this->bulk_quantity = 1;
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
        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) (auth()->user()?->branch_id ?? 0);
            $branches = Branch::query()
                ->whereKey((int) (auth()->user()?->branch_id ?? 0))
                ->where('is_active', true)
                ->get();
        } else {
            $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();
        }
        $products = Product::query()->orderBy('name')->get();

        $selectedProduct = null;
        if ($this->product_id > 0) {
            $selectedProduct = Product::query()->with(['bulkType.bulkUnit'])->find($this->product_id);
        }

        $stocks = ProductStock::query()
            ->with(['product'])
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->orderBy('products.name')
            ->select('product_stocks.*')
            ->get();

        $receipts = StockInReceipt::query()
            ->with(['branch', 'user'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
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
            'selectedProduct' => $selectedProduct,
            'stocks' => $stocks,
            'receipts' => $receipts,
            'selectedReceipt' => $selectedReceipt,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }
}
