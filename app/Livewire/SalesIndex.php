<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesItem;
use App\Models\SalesReceipt;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class SalesIndex extends Component
{
    public int $branch_id = 0;
    public int $product_id = 0;

    public string $product_search = '';
    public string $sales_search = '';

    public string $entry_mode = 'unit';
    public int $entry_quantity = 1;
    public int $bulk_quantity = 1;

    public bool $isSuperAdmin = false;

    public int $auth_user_id = 0;

    /**
     * @var array<int, array{product_id:int,name:string,unit_price:string,quantity:int,entry_mode:string,bulk_quantity:?int,units_per_bulk:?int,bulk_type_id:?int}>
     */
    public array $cart = [];

    public string $payment_method = 'cash';
    public ?string $amount_paid = null;
    public ?string $notes = null;

    public int $selected_sale_id = 0;

    public bool $show_sale_modal = false;

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
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->auth_user_id = (int) ($user?->id ?? 0);

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        } else {
            $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        }

        $this->product_id = (int) (Product::query()
            ->where('status', 'active')
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->value('id') ?? 0);
        $this->entry_mode = 'unit';
        $this->entry_quantity = 1;
        $this->bulk_quantity = 1;
        $this->payment_method = 'cash';
        $this->amount_paid = null;
        $this->notes = null;
        $this->selected_sale_id = 0;

        $this->product_search = '';
        $this->sales_search = '';
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;
            $this->cart = [];
            $this->selected_sale_id = 0;
            $this->amount_paid = null;
            $this->notes = null;
            $this->entry_mode = 'unit';
            $this->entry_quantity = 1;
            $this->bulk_quantity = 1;
            $this->product_search = '';
            $this->sales_search = '';
        }

        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
    }

    public function updatedProductId(): void
    {
        $product = Product::query()
            ->with(['bulkType'])
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->find($this->product_id);
        if ($product && (bool) $product->bulk_enabled) {
            $this->entry_mode = 'bulk';
            $this->bulk_quantity = max(1, (int) $this->bulk_quantity);
        } else {
            $this->entry_mode = 'unit';
            $this->entry_quantity = max(1, (int) $this->entry_quantity);
        }
    }

    public function updatedBranchId(): void
    {
        if (! $this->isSuperAdmin) {
            return;
        }

        $this->cart = [];
        $this->selected_sale_id = 0;

        $this->product_id = (int) (Product::query()
            ->where('status', 'active')
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->value('id') ?? 0);

        $this->updatedProductId();
    }

    public function addProduct(): void
    {
        $this->resetErrorBag('cart');

        if ($this->branch_id <= 0 || $this->product_id <= 0) {
            return;
        }

        $product = Product::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->find($this->product_id);
        if (! $product) {
            return;
        }

        $bulkTypeId = null;
        $unitsPerBulk = null;
        $bulkQty = null;
        $unitsQty = 0;

        if ($this->entry_mode === 'bulk') {
            if (! (bool) $product->bulk_enabled) {
                $this->addError('cart', 'Bulk mode is not enabled for this product.');
                return;
            }

            $product = Product::query()
                ->with(['bulkType'])
                ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
                ->find($this->product_id);
            if (! $product || ! $product->bulkType) {
                $this->addError('cart', 'Bulk type is not configured for this product.');
                return;
            }

            $bulkTypeId = (int) $product->bulkType->id;
            $unitsPerBulk = (int) $product->bulkType->units_per_bulk;
            $bulkQty = max(1, (int) $this->bulk_quantity);

            if ($unitsPerBulk <= 0) {
                $this->addError('cart', 'Invalid units per bulk configuration.');
                return;
            }

            $unitsQty = $bulkQty * $unitsPerBulk;
        } else {
            $unitsQty = max(1, (int) $this->entry_quantity);
        }

        if (isset($this->cart[$product->id])) {
            $existingMode = (string) ($this->cart[$product->id]['entry_mode'] ?? 'unit');
            if ($existingMode === 'bulk' && $unitsPerBulk) {
                $this->cart[$product->id]['bulk_quantity'] = (int) ($this->cart[$product->id]['bulk_quantity'] ?? 0) + (int) ($bulkQty ?? 1);
                $this->cart[$product->id]['quantity'] = (int) $this->cart[$product->id]['bulk_quantity'] * (int) $this->cart[$product->id]['units_per_bulk'];
            } else {
                $this->cart[$product->id]['quantity'] = (int) $this->cart[$product->id]['quantity'] + $unitsQty;
            }
            return;
        }

        $this->cart[$product->id] = [
            'product_id' => (int) $product->id,
            'name' => (string) $product->name,
            'unit_price' => (string) $product->selling_price,
            'quantity' => $unitsQty,
            'entry_mode' => $this->entry_mode,
            'bulk_quantity' => $bulkQty,
            'units_per_bulk' => $unitsPerBulk,
            'bulk_type_id' => $bulkTypeId,
        ];
    }

    public function incrementItem(int $productId): void
    {
        $this->resetErrorBag('cart');

        if (! isset($this->cart[$productId])) {
            return;
        }

        $mode = (string) ($this->cart[$productId]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $this->cart[$productId]['bulk_quantity'] = (int) ($this->cart[$productId]['bulk_quantity'] ?? 0) + 1;
            $this->cart[$productId]['quantity'] = (int) $this->cart[$productId]['bulk_quantity'] * (int) ($this->cart[$productId]['units_per_bulk'] ?? 0);
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

        $mode = (string) ($this->cart[$productId]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $newBulkQty = (int) ($this->cart[$productId]['bulk_quantity'] ?? 0) - 1;
            if ($newBulkQty <= 0) {
                unset($this->cart[$productId]);
                return;
            }

            $this->cart[$productId]['bulk_quantity'] = $newBulkQty;
            $this->cart[$productId]['quantity'] = $newBulkQty * (int) ($this->cart[$productId]['units_per_bulk'] ?? 0);
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
        $mode = (string) ($this->cart[$productId]['entry_mode'] ?? 'unit');

        if ($qty <= 0) {
            unset($this->cart[$productId]);
            return;
        }

        if ($mode === 'bulk') {
            $this->cart[$productId]['bulk_quantity'] = $qty;
            $this->cart[$productId]['quantity'] = $qty * (int) ($this->cart[$productId]['units_per_bulk'] ?? 0);
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

        if (! $this->isSuperAdmin) {
            $data['branch_id'] = (int) (auth()->user()?->branch_id ?? 0);
            $this->branch_id = (int) $data['branch_id'];
        }

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
                    'cogs_total' => '0.00',
                    'profit_total' => '0.00',
                    'amount_paid' => (string) $amountPaid,
                    'change_due' => (string) $changeDue,
                    'notes' => $data['notes'] ?? null,
                ]);

                $cogsTotal = 0.0;
                $profitTotal = 0.0;

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

                    $beforeStock = (int) $stock->current_stock;
                    $unitCostFloat = $stock->cost_price !== null ? (float) $stock->cost_price : 0.0;
                    $unitCost = $stock->cost_price !== null ? (string) $stock->cost_price : null;

                    $stock->current_stock = (int) $stock->current_stock - (int) $item['quantity'];
                    $stock->save();

                    $afterStock = (int) $stock->current_stock;

                    $lineTotal = (float) $item['unit_price'] * (int) $item['quantity'];
                    $lineCost = $unitCostFloat * (int) $item['quantity'];
                    $lineProfit = $lineTotal - $lineCost;

                    $cogsTotal += $lineCost;
                    $profitTotal += $lineProfit;

                    SalesItem::query()->create([
                        'sales_receipt_id' => $receipt->id,
                        'product_id' => (int) $item['product_id'],
                        'entry_mode' => (string) ($item['entry_mode'] ?? 'unit'),
                        'bulk_quantity' => $item['bulk_quantity'] ?? null,
                        'units_per_bulk' => $item['units_per_bulk'] ?? null,
                        'bulk_type_id' => $item['bulk_type_id'] ?? null,
                        'quantity' => (int) $item['quantity'],
                        'unit_price' => (string) $item['unit_price'],
                        'unit_cost' => number_format($unitCostFloat, 2, '.', ''),
                        'line_total' => number_format($lineTotal, 2, '.', ''),
                        'line_cost' => number_format($lineCost, 2, '.', ''),
                        'line_profit' => number_format($lineProfit, 2, '.', ''),
                    ]);

                    StockMovement::query()->create([
                        'branch_id' => (int) $data['branch_id'],
                        'product_id' => (int) $item['product_id'],
                        'user_id' => auth()->id(),
                        'movement_type' => 'OUT',
                        'quantity' => (int) $item['quantity'],
                        'before_stock' => $beforeStock,
                        'after_stock' => $afterStock,
                        'unit_cost' => $unitCost,
                        'unit_price' => (string) $item['unit_price'],
                        'stock_in_receipt_id' => null,
                        'sales_receipt_id' => (int) $receipt->id,
                        'moved_at' => now(),
                        'notes' => null,
                    ]);
                }

                $receipt->cogs_total = number_format($cogsTotal, 2, '.', '');
                $receipt->profit_total = number_format($profitTotal, 2, '.', '');
                $receipt->save();

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

    public function openSaleModal(int $id): void
    {
        $this->selected_sale_id = $id;
        $this->show_sale_modal = true;
    }

    public function closeSaleModal(): void
    {
        $this->show_sale_modal = false;
    }

    public function render()
    {
        $this->syncAuthContext();

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) (auth()->user()?->branch_id ?? 0);
            $branches = Branch::query()
                ->whereKey((int) (auth()->user()?->branch_id ?? 0))
                ->where('is_active', true)
                ->get();
        } else {
            $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();
        }

        $products = Product::query()
            ->where('status', 'active')
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when(trim($this->product_search) !== '', function ($q) {
                $term = '%' . trim($this->product_search) . '%';
                $q->where('name', 'like', $term);
            })
            ->orderBy('name')
            ->get();

        $selectedProduct = null;
        if ($this->product_id > 0) {
            $selectedProduct = Product::query()
                ->with(['bulkType.bulkUnit'])
                ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
                ->find($this->product_id);
        }

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
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->when(trim($this->sales_search) !== '', function ($q) {
                $term = '%' . trim($this->sales_search) . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('receipt_no', 'like', $term)
                        ->orWhereHas('branch', fn ($qb) => $qb->where('name', 'like', $term))
                        ->orWhereHas('user', fn ($qu) => $qu->where('name', 'like', $term));
                });
            })
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
            'selectedProduct' => $selectedProduct,
            'stockMap' => $stockMap,
            'cartItems' => $cartItems,
            'subTotal' => $subTotal,
            'grandTotal' => $grandTotal,
            'changeDue' => $changeDue,
            'sales' => $sales,
            'selectedSale' => $selectedSale,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }
}
