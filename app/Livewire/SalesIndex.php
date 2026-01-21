<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesItem;
use App\Models\SalesReceipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class SalesIndex extends Component
{
    public int $branch_id = 0;
    public int $product_id = 0;

    /**
     * @var array<int, array{product_id:int,name:string,unit_price:string,quantity:int}>
     */
    public array $cart = [];

    public string $payment_method = 'cash';
    public ?string $amount_paid = null;
    public ?string $notes = null;

    public int $selected_sale_id = 0;

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'min:1'],
            'payment_method' => ['required', 'string', 'in:cash,card'],
            'amount_paid' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function mount(): void
    {
        $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        $this->product_id = (int) (Product::query()->where('status', 'active')->orderBy('name')->value('id') ?? 0);
        $this->payment_method = 'cash';
        $this->amount_paid = null;
        $this->notes = null;
        $this->selected_sale_id = 0;
    }

    public function addProduct(): void
    {
        $this->resetErrorBag('cart');

        if ($this->branch_id <= 0 || $this->product_id <= 0) {
            return;
        }

        $product = Product::query()->find($this->product_id);
        if (! $product) {
            return;
        }

        if (isset($this->cart[$product->id])) {
            $this->cart[$product->id]['quantity'] = (int) $this->cart[$product->id]['quantity'] + 1;
            return;
        }

        $this->cart[$product->id] = [
            'product_id' => (int) $product->id,
            'name' => (string) $product->name,
            'unit_price' => (string) $product->selling_price,
            'quantity' => 1,
        ];
    }

    public function incrementItem(int $productId): void
    {
        $this->resetErrorBag('cart');

        if (! isset($this->cart[$productId])) {
            return;
        }

        $this->cart[$productId]['quantity'] = (int) $this->cart[$productId]['quantity'] + 1;
    }

    public function decrementItem(int $productId): void
    {
        $this->resetErrorBag('cart');

        if (! isset($this->cart[$productId])) {
            return;
        }

        $newQty = (int) $this->cart[$productId]['quantity'] - 1;
        if ($newQty <= 0) {
            unset($this->cart[$productId]);
            return;
        }

        $this->cart[$productId]['quantity'] = $newQty;
    }

    public function removeItem(int $productId): void
    {
        unset($this->cart[$productId]);
    }

    public function setQuantity(int $productId, mixed $quantity): void
    {
        $this->resetErrorBag('cart');

        if (! isset($this->cart[$productId])) {
            return;
        }

        $qty = (int) $quantity;
        if ($qty <= 0) {
            unset($this->cart[$productId]);
            return;
        }

        $this->cart[$productId]['quantity'] = $qty;
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->amount_paid = null;
        $this->notes = null;
        $this->resetErrorBag('cart');
    }

    public function finalizeSale(): void
    {
        $this->resetErrorBag('cart');

        $data = $this->validate();

        if ($this->branch_id <= 0 || count($this->cart) === 0) {
            return;
        }

        $cartItems = array_values($this->cart);

        $subTotal = 0.0;
        foreach ($cartItems as $item) {
            $subTotal += (float) $item['unit_price'] * (int) $item['quantity'];
        }

        $grandTotal = $subTotal;
        $amountPaid = ($data['amount_paid'] !== null && $data['amount_paid'] !== '') ? (float) $data['amount_paid'] : 0.0;
        $changeDue = max(0.0, $amountPaid - $grandTotal);

        if ($data['payment_method'] === 'cash' && $amountPaid < $grandTotal) {
            $this->addError('amount_paid', 'Amount paid must be greater than or equal to grand total.');
            return;
        }

        try {
            $saleId = DB::transaction(function () use ($cartItems, $data, $subTotal, $grandTotal, $amountPaid, $changeDue) {
                foreach ($cartItems as $item) {
                    $stock = ProductStock::query()
                        ->where('branch_id', (int) $data['branch_id'])
                        ->where('product_id', (int) $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    $available = (int) ($stock?->current_stock ?? 0);
                    if ($available < (int) $item['quantity']) {
                        throw ValidationException::withMessages([
                            'cart' => 'Insufficient stock for ' . $item['name'] . '. Available: ' . $available . ', Requested: ' . (int) $item['quantity'] . '.',
                        ]);
                    }
                }

                $receipt = SalesReceipt::query()->create([
                    'receipt_no' => 'SL-' . strtoupper(Str::random(10)),
                    'branch_id' => (int) $data['branch_id'],
                    'user_id' => auth()->id(),
                    'sold_at' => now(),
                    'payment_method' => $data['payment_method'],
                    'sub_total' => (string) $subTotal,
                    'discount_total' => '0.00',
                    'grand_total' => (string) $grandTotal,
                    'amount_paid' => (string) $amountPaid,
                    'change_due' => (string) $changeDue,
                    'notes' => $data['notes'] ?? null,
                ]);

                foreach ($cartItems as $item) {
                    $stock = ProductStock::query()
                        ->where('branch_id', (int) $data['branch_id'])
                        ->where('product_id', (int) $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if (! $stock) {
                        $stock = ProductStock::query()->create([
                            'branch_id' => (int) $data['branch_id'],
                            'product_id' => (int) $item['product_id'],
                            'current_stock' => 0,
                            'minimum_stock' => 0,
                            'cost_price' => null,
                        ]);
                    }

                    $stock->current_stock = (int) $stock->current_stock - (int) $item['quantity'];
                    $stock->save();

                    SalesItem::query()->create([
                        'sales_receipt_id' => $receipt->id,
                        'product_id' => (int) $item['product_id'],
                        'quantity' => (int) $item['quantity'],
                        'unit_price' => (string) $item['unit_price'],
                        'line_total' => (string) ((float) $item['unit_price'] * (int) $item['quantity']),
                    ]);
                }

                return (int) $receipt->id;
            });
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
            return;
        }

        $this->clearCart();
        $this->selected_sale_id = (int) $saleId;
        session()->flash('status', 'Sale posted successfully.');
    }

    public function selectSale(int $id): void
    {
        $this->selected_sale_id = $id;
    }

    public function render()
    {
        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();

        $products = Product::query()->where('status', 'active')->orderBy('name')->get();

        $stockMap = ProductStock::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->pluck('current_stock', 'product_id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $cartItems = array_values($this->cart);
        $subTotal = 0.0;
        foreach ($cartItems as $item) {
            $subTotal += (float) $item['unit_price'] * (int) $item['quantity'];
        }
        $grandTotal = $subTotal;
        $amountPaid = ($this->amount_paid !== null && $this->amount_paid !== '') ? (float) $this->amount_paid : 0.0;
        $changeDue = max(0.0, $amountPaid - $grandTotal);

        $sales = SalesReceipt::query()
            ->with(['branch', 'user'])
            ->orderByDesc('sold_at')
            ->limit(15)
            ->get();

        $selectedSale = null;
        if ($this->selected_sale_id > 0) {
            $selectedSale = SalesReceipt::query()
                ->with(['branch', 'user', 'items.product'])
                ->find($this->selected_sale_id);
        }

        return view('livewire.sales-index', [
            'branches' => $branches,
            'products' => $products,
            'stockMap' => $stockMap,
            'cartItems' => $cartItems,
            'subTotal' => $subTotal,
            'grandTotal' => $grandTotal,
            'changeDue' => $changeDue,
            'sales' => $sales,
            'selectedSale' => $selectedSale,
        ]);
    }
}
