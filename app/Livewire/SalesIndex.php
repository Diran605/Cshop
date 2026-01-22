<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\SalesItem;
use App\Models\SalesReceipt;
use App\Models\StockMovement;
use Carbon\Carbon;
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

    public string $sales_date_from;
    public string $sales_date_to;
    public string $sales_status = 'active';

    /**
     * @var array<int>
     */
    public array $selected_sales = [];

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

    public bool $show_edit_modal = false;
    public bool $show_void_modal = false;

    public int $editing_sale_id = 0;
    public int $edit_branch_id = 0;

    public array $edit_cart = [];

    public int $edit_product_id = 0;
    public string $edit_entry_mode = 'unit';
    public int $edit_entry_quantity = 1;
    public int $edit_bulk_quantity = 1;

    public string $edit_payment_method = 'cash';
    public ?string $edit_amount_paid = null;
    public ?string $edit_notes = null;

    public int $pending_void_sale_id = 0;
    public ?string $void_reason = null;

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

        $today = Carbon::today();
        $this->sales_date_from = $today->toDateString();
        $this->sales_date_to = $today->toDateString();
        $this->sales_status = 'active';
        $this->selected_sales = [];

        $this->show_edit_modal = false;
        $this->show_void_modal = false;
        $this->editing_sale_id = 0;
        $this->edit_branch_id = 0;
        $this->edit_cart = [];
        $this->edit_product_id = 0;
        $this->edit_entry_mode = 'unit';
        $this->edit_entry_quantity = 1;
        $this->edit_bulk_quantity = 1;
        $this->edit_payment_method = 'cash';
        $this->edit_amount_paid = null;
        $this->edit_notes = null;
        $this->pending_void_sale_id = 0;
        $this->void_reason = null;
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

            $today = Carbon::today();
            $this->sales_date_from = $today->toDateString();
            $this->sales_date_to = $today->toDateString();
            $this->sales_status = 'active';
            $this->selected_sales = [];

            $this->show_edit_modal = false;
            $this->show_void_modal = false;
            $this->editing_sale_id = 0;
            $this->edit_branch_id = 0;
            $this->edit_cart = [];
            $this->edit_product_id = 0;
            $this->edit_entry_mode = 'unit';
            $this->edit_entry_quantity = 1;
            $this->edit_bulk_quantity = 1;
            $this->edit_payment_method = 'cash';
            $this->edit_amount_paid = null;
            $this->edit_notes = null;
            $this->pending_void_sale_id = 0;
            $this->void_reason = null;
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

    public function openEditModal(int $id): void
    {
        $sale = SalesReceipt::query()
            ->with(['items.product'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        if ($sale->voided_at) {
            return;
        }

        $this->editing_sale_id = (int) $sale->id;
        $this->edit_branch_id = (int) $sale->branch_id;
        $this->edit_payment_method = (string) ($sale->payment_method ?? 'cash');
        $this->edit_amount_paid = $sale->amount_paid !== null ? (string) $sale->amount_paid : null;
        $this->edit_notes = $sale->notes;

        $this->edit_cart = [];
        foreach ($sale->items as $item) {
            $this->edit_cart[(int) $item->product_id] = [
                'product_id' => (int) $item->product_id,
                'name' => (string) ($item->product?->name ?? '-'),
                'unit_price' => (string) $item->unit_price,
                'quantity' => (int) $item->quantity,
                'entry_mode' => (string) ($item->entry_mode ?? 'unit'),
                'bulk_quantity' => $item->bulk_quantity !== null ? (int) $item->bulk_quantity : null,
                'units_per_bulk' => $item->units_per_bulk !== null ? (int) $item->units_per_bulk : null,
                'bulk_type_id' => $item->bulk_type_id !== null ? (int) $item->bulk_type_id : null,
            ];
        }

        $this->edit_product_id = 0;
        $this->edit_entry_mode = 'unit';
        $this->edit_entry_quantity = 1;
        $this->edit_bulk_quantity = 1;

        $this->resetErrorBag();
        $this->show_edit_modal = true;
    }

    public function closeEditModal(): void
    {
        $this->show_edit_modal = false;
        $this->editing_sale_id = 0;
        $this->edit_branch_id = 0;
        $this->edit_cart = [];
        $this->edit_product_id = 0;
        $this->edit_entry_mode = 'unit';
        $this->edit_entry_quantity = 1;
        $this->edit_bulk_quantity = 1;
        $this->edit_payment_method = 'cash';
        $this->edit_amount_paid = null;
        $this->edit_notes = null;
        $this->resetErrorBag();
    }

    public function updatedEditProductId(): void
    {
        $product = Product::query()
            ->with(['bulkType'])
            ->where('status', 'active')
            ->when($this->edit_branch_id > 0, fn ($q) => $q->where('branch_id', $this->edit_branch_id))
            ->find($this->edit_product_id);

        if ($product && (bool) $product->bulk_enabled) {
            $this->edit_entry_mode = 'bulk';
            $this->edit_bulk_quantity = max(1, (int) $this->edit_bulk_quantity);
        } else {
            $this->edit_entry_mode = 'unit';
            $this->edit_entry_quantity = max(1, (int) $this->edit_entry_quantity);
        }
    }

    public function addEditProduct(): void
    {
        $this->resetErrorBag('edit_cart');

        if ($this->editing_sale_id <= 0 || $this->edit_branch_id <= 0 || $this->edit_product_id <= 0) {
            return;
        }

        $product = Product::query()
            ->with(['bulkType'])
            ->where('status', 'active')
            ->when($this->edit_branch_id > 0, fn ($q) => $q->where('branch_id', $this->edit_branch_id))
            ->find($this->edit_product_id);

        if (! $product) {
            return;
        }

        $bulkTypeId = null;
        $unitsPerBulk = null;
        $bulkQty = null;
        $unitsQty = 0;

        if ($this->edit_entry_mode === 'bulk') {
            if (! (bool) $product->bulk_enabled || ! $product->bulkType) {
                $this->addError('edit_cart', 'Bulk type is not configured for this product.');
                return;
            }

            $bulkTypeId = (int) $product->bulkType->id;
            $unitsPerBulk = (int) $product->bulkType->units_per_bulk;
            $bulkQty = max(1, (int) $this->edit_bulk_quantity);

            if ($unitsPerBulk <= 0) {
                $this->addError('edit_cart', 'Invalid units per bulk configuration.');
                return;
            }

            $unitsQty = $bulkQty * $unitsPerBulk;
        } else {
            $unitsQty = max(1, (int) $this->edit_entry_quantity);
        }

        if (isset($this->edit_cart[$product->id])) {
            $existingMode = (string) ($this->edit_cart[$product->id]['entry_mode'] ?? 'unit');
            if ($existingMode === 'bulk' && $unitsPerBulk) {
                $this->edit_cart[$product->id]['bulk_quantity'] = (int) ($this->edit_cart[$product->id]['bulk_quantity'] ?? 0) + (int) ($bulkQty ?? 1);
                $this->edit_cart[$product->id]['quantity'] = (int) $this->edit_cart[$product->id]['bulk_quantity'] * (int) $this->edit_cart[$product->id]['units_per_bulk'];
            } else {
                $this->edit_cart[$product->id]['quantity'] = (int) $this->edit_cart[$product->id]['quantity'] + $unitsQty;
            }
            return;
        }

        $this->edit_cart[$product->id] = [
            'product_id' => (int) $product->id,
            'name' => (string) $product->name,
            'unit_price' => (string) $product->selling_price,
            'quantity' => $unitsQty,
            'entry_mode' => $this->edit_entry_mode,
            'bulk_quantity' => $bulkQty,
            'units_per_bulk' => $unitsPerBulk,
            'bulk_type_id' => $bulkTypeId,
        ];
    }

    public function incrementEditItem(int $productId): void
    {
        $this->resetErrorBag('edit_cart');

        if (! isset($this->edit_cart[$productId])) {
            return;
        }

        $mode = (string) ($this->edit_cart[$productId]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $this->edit_cart[$productId]['bulk_quantity'] = (int) ($this->edit_cart[$productId]['bulk_quantity'] ?? 0) + 1;
            $this->edit_cart[$productId]['quantity'] = (int) $this->edit_cart[$productId]['bulk_quantity'] * (int) ($this->edit_cart[$productId]['units_per_bulk'] ?? 0);
            return;
        }

        $this->edit_cart[$productId]['quantity'] = (int) $this->edit_cart[$productId]['quantity'] + 1;
    }

    public function decrementEditItem(int $productId): void
    {
        $this->resetErrorBag('edit_cart');

        if (! isset($this->edit_cart[$productId])) {
            return;
        }

        $mode = (string) ($this->edit_cart[$productId]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $newBulkQty = (int) ($this->edit_cart[$productId]['bulk_quantity'] ?? 0) - 1;
            if ($newBulkQty <= 0) {
                unset($this->edit_cart[$productId]);
                return;
            }

            $this->edit_cart[$productId]['bulk_quantity'] = $newBulkQty;
            $this->edit_cart[$productId]['quantity'] = $newBulkQty * (int) ($this->edit_cart[$productId]['units_per_bulk'] ?? 0);
            return;
        }

        $newQty = (int) ($this->edit_cart[$productId]['quantity'] ?? 0) - 1;
        if ($newQty <= 0) {
            unset($this->edit_cart[$productId]);
            return;
        }

        $this->edit_cart[$productId]['quantity'] = $newQty;
    }

    public function setEditQuantity(int $productId, mixed $quantity): void
    {
        $this->resetErrorBag('edit_cart');

        if (! isset($this->edit_cart[$productId])) {
            return;
        }

        $qty = (int) $quantity;
        if ($qty <= 0) {
            unset($this->edit_cart[$productId]);
            return;
        }

        $mode = (string) ($this->edit_cart[$productId]['entry_mode'] ?? 'unit');
        if ($mode === 'bulk') {
            $this->edit_cart[$productId]['bulk_quantity'] = $qty;
            $this->edit_cart[$productId]['quantity'] = $qty * (int) ($this->edit_cart[$productId]['units_per_bulk'] ?? 0);
            return;
        }

        $this->edit_cart[$productId]['quantity'] = $qty;
    }

    public function setEditUnitPrice(int $productId, mixed $unitPrice): void
    {
        $this->resetErrorBag('edit_cart');

        if (! isset($this->edit_cart[$productId])) {
            return;
        }

        $v = (float) $unitPrice;
        if ($v < 0) {
            $v = 0;
        }

        $this->edit_cart[$productId]['unit_price'] = number_format($v, 2, '.', '');
    }

    public function removeEditItem(int $productId): void
    {
        unset($this->edit_cart[$productId]);
    }

    public function openVoidModal(int $id): void
    {
        $sale = SalesReceipt::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        if ($sale->voided_at) {
            return;
        }

        $this->pending_void_sale_id = (int) $sale->id;
        $this->void_reason = null;
        $this->resetErrorBag();
        $this->show_void_modal = true;
    }

    public function closeVoidModal(): void
    {
        $this->show_void_modal = false;
        $this->pending_void_sale_id = 0;
        $this->void_reason = null;
        $this->resetErrorBag();
    }

    public function confirmVoidSale(): void
    {
        $this->resetErrorBag();

        if ($this->pending_void_sale_id <= 0) {
            return;
        }

        try {
            DB::transaction(function () {
                $receipt = SalesReceipt::query()
                    ->whereKey($this->pending_void_sale_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($receipt->voided_at) {
                    return;
                }

                if (! $this->isSuperAdmin) {
                    abort_unless((int) (auth()->user()?->branch_id ?? 0) === (int) $receipt->branch_id, 403);
                }

                $receipt->load(['items']);

                foreach ($receipt->items as $item) {
                    $stock = ProductStock::query()->firstOrCreate(
                        ['branch_id' => (int) $receipt->branch_id, 'product_id' => (int) $item->product_id],
                        ['current_stock' => 0, 'minimum_stock' => 0, 'cost_price' => null]
                    );

                    $stock = ProductStock::query()->whereKey($stock->id)->lockForUpdate()->firstOrFail();

                    $beforeStock = (int) $stock->current_stock;
                    $stock->current_stock = $beforeStock + (int) $item->quantity;
                    $stock->save();

                    StockMovement::query()->create([
                        'branch_id' => (int) $receipt->branch_id,
                        'product_id' => (int) $item->product_id,
                        'user_id' => auth()->id(),
                        'movement_type' => 'IN',
                        'quantity' => (int) $item->quantity,
                        'before_stock' => $beforeStock,
                        'after_stock' => (int) $stock->current_stock,
                        'unit_cost' => $item->unit_cost !== null ? (string) $item->unit_cost : null,
                        'unit_price' => $item->unit_price !== null ? (string) $item->unit_price : null,
                        'stock_in_receipt_id' => null,
                        'sales_receipt_id' => (int) $receipt->id,
                        'moved_at' => now(),
                        'notes' => 'SALE VOID',
                    ]);
                }

                $receipt->voided_at = now();
                $receipt->voided_by = auth()->id();
                $receipt->void_reason = $this->void_reason;
                $receipt->save();
            });
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
            return;
        }

        $this->closeVoidModal();
        session()->flash('status', 'Sale voided successfully.');
    }

    public function saveEdit(): void
    {
        $this->resetErrorBag();

        if ($this->editing_sale_id <= 0) {
            return;
        }

        $items = array_values($this->edit_cart);
        if (count($items) === 0) {
            $this->addError('edit_cart', 'Cart is empty.');
            return;
        }

        $subTotal = 0.0;
        foreach ($items as $item) {
            $subTotal += (float) $item['unit_price'] * (int) $item['quantity'];
        }

        $grandTotal = $subTotal;
        $amountPaid = ($this->edit_amount_paid !== null && $this->edit_amount_paid !== '') ? (float) $this->edit_amount_paid : 0.0;
        $changeDue = max(0.0, $amountPaid - $grandTotal);

        if ((string) $this->edit_payment_method === 'cash' && $amountPaid < $grandTotal) {
            $this->addError('edit_amount_paid', 'Amount paid must be greater than or equal to grand total.');
            return;
        }

        try {
            DB::transaction(function () use ($items, $subTotal, $grandTotal, $amountPaid, $changeDue) {
                $receipt = SalesReceipt::query()
                    ->whereKey($this->editing_sale_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($receipt->voided_at) {
                    return;
                }

                if (! $this->isSuperAdmin) {
                    abort_unless((int) (auth()->user()?->branch_id ?? 0) === (int) $receipt->branch_id, 403);
                }

                $receipt->load(['items']);

                foreach ($receipt->items as $oldItem) {
                    $stock = ProductStock::query()->firstOrCreate(
                        ['branch_id' => (int) $receipt->branch_id, 'product_id' => (int) $oldItem->product_id],
                        ['current_stock' => 0, 'minimum_stock' => 0, 'cost_price' => null]
                    );

                    $stock = ProductStock::query()->whereKey($stock->id)->lockForUpdate()->firstOrFail();

                    $beforeStock = (int) $stock->current_stock;
                    $stock->current_stock = $beforeStock + (int) $oldItem->quantity;
                    $stock->save();

                    StockMovement::query()->create([
                        'branch_id' => (int) $receipt->branch_id,
                        'product_id' => (int) $oldItem->product_id,
                        'user_id' => auth()->id(),
                        'movement_type' => 'IN',
                        'quantity' => (int) $oldItem->quantity,
                        'before_stock' => $beforeStock,
                        'after_stock' => (int) $stock->current_stock,
                        'unit_cost' => $oldItem->unit_cost !== null ? (string) $oldItem->unit_cost : null,
                        'unit_price' => $oldItem->unit_price !== null ? (string) $oldItem->unit_price : null,
                        'stock_in_receipt_id' => null,
                        'sales_receipt_id' => (int) $receipt->id,
                        'moved_at' => now(),
                        'notes' => 'SALE EDIT REVERSAL',
                    ]);
                }

                SalesItem::query()->where('sales_receipt_id', (int) $receipt->id)->delete();

                foreach ($items as $item) {
                    $stock = ProductStock::query()
                        ->where('branch_id', (int) $receipt->branch_id)
                        ->where('product_id', (int) $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    $available = (int) ($stock?->current_stock ?? 0);
                    if ($available < (int) $item['quantity']) {
                        throw ValidationException::withMessages([
                            'edit_cart' => 'Insufficient stock for ' . $item['name'] . '. Available: ' . $available . ', Requested: ' . (int) $item['quantity'] . '.',
                        ]);
                    }
                }

                $cogsTotal = 0.0;
                $profitTotal = 0.0;

                foreach ($items as $item) {
                    $stock = ProductStock::query()
                        ->where('branch_id', (int) $receipt->branch_id)
                        ->where('product_id', (int) $item['product_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

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
                        'sales_receipt_id' => (int) $receipt->id,
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
                        'branch_id' => (int) $receipt->branch_id,
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
                        'notes' => 'SALE EDIT',
                    ]);
                }

                $receipt->payment_method = (string) $this->edit_payment_method;
                $receipt->sub_total = (string) $subTotal;
                $receipt->discount_total = '0.00';
                $receipt->grand_total = (string) $grandTotal;
                $receipt->cogs_total = number_format($cogsTotal, 2, '.', '');
                $receipt->profit_total = number_format($profitTotal, 2, '.', '');
                $receipt->amount_paid = (string) $amountPaid;
                $receipt->change_due = (string) $changeDue;
                $receipt->notes = $this->edit_notes;
                $receipt->save();
            });
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
            return;
        }

        $this->closeEditModal();
        session()->flash('status', 'Sale updated successfully.');
    }

    public function clearSelectedSales(): void
    {
        $this->selected_sales = [];
    }

    public function selectAllSalesForDay(string $day): void
    {
        $from = Carbon::parse($day)->startOfDay();
        $to = Carbon::parse($day)->endOfDay();

        $q = SalesReceipt::query()
            ->whereBetween('sold_at', [$from, $to])
            ->when(! $this->isSuperAdmin, fn ($qq) => $qq->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($qq) => $qq->where('branch_id', $this->branch_id));

        if ($this->sales_status === 'active') {
            $q->whereNull('voided_at');
        } elseif ($this->sales_status === 'voided') {
            $q->whereNotNull('voided_at');
        }

        if (trim($this->sales_search) !== '') {
            $term = '%' . trim($this->sales_search) . '%';
            $q->where(function ($qq) use ($term) {
                $qq->where('receipt_no', 'like', $term)
                    ->orWhereHas('branch', fn ($qb) => $qb->where('name', 'like', $term))
                    ->orWhereHas('user', fn ($qu) => $qu->where('name', 'like', $term));
            });
        }

        $this->selected_sales = $q->orderByDesc('sold_at')->pluck('id')->map(fn ($v) => (int) $v)->all();
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

        $editProducts = Product::query()
            ->with(['bulkType.bulkUnit'])
            ->where('status', 'active')
            ->when(($this->edit_branch_id > 0), fn ($q) => $q->where('branch_id', $this->edit_branch_id))
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

        $editStockMap = ProductStock::query()
            ->when($this->edit_branch_id > 0, fn ($q) => $q->where('branch_id', $this->edit_branch_id))
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

        $editCartItems = array_values($this->edit_cart);
        $editSubTotal = 0.0;
        foreach ($editCartItems as $item) {
            $editSubTotal += (float) $item['unit_price'] * (int) $item['quantity'];
        }
        $editGrandTotal = $editSubTotal;
        $editAmountPaid = ($this->edit_amount_paid !== null && $this->edit_amount_paid !== '') ? (float) $this->edit_amount_paid : 0.0;
        $editChangeDue = max(0.0, $editAmountPaid - $editGrandTotal);

        $sales = SalesReceipt::query()
            ->with(['branch', 'user'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when(trim($this->sales_search) !== '', function ($q) {
                $term = '%' . trim($this->sales_search) . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('receipt_no', 'like', $term)
                        ->orWhereHas('branch', fn ($qb) => $qb->where('name', 'like', $term))
                        ->orWhereHas('user', fn ($qu) => $qu->where('name', 'like', $term));
                });
            })
            ->when($this->sales_date_from !== '' && $this->sales_date_to !== '', function ($q) {
                $from = Carbon::parse($this->sales_date_from)->startOfDay();
                $to = Carbon::parse($this->sales_date_to)->endOfDay();
                $q->whereBetween('sold_at', [$from, $to]);
            })
            ->when($this->sales_status === 'active', fn ($q) => $q->whereNull('voided_at'))
            ->when($this->sales_status === 'voided', fn ($q) => $q->whereNotNull('voided_at'))
            ->orderByDesc('sold_at')
            ->limit(300)
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
            'editProducts' => $editProducts,
            'selectedProduct' => $selectedProduct,
            'stockMap' => $stockMap,
            'editStockMap' => $editStockMap,
            'cartItems' => $cartItems,
            'subTotal' => $subTotal,
            'grandTotal' => $grandTotal,
            'changeDue' => $changeDue,
            'editCartItems' => $editCartItems,
            'editSubTotal' => $editSubTotal,
            'editGrandTotal' => $editGrandTotal,
            'editChangeDue' => $editChangeDue,
            'sales' => $sales,
            'selectedSale' => $selectedSale,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }
}
