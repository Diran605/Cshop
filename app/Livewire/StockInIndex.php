<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\StockInItem;
use App\Models\StockInReceipt;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class StockInIndex extends Component
{
    public int $branch_id = 0;
    public int $product_id = 0;

    public string $product_search = '';
    public string $stock_search = '';
    public string $receipt_search = '';

    public string $receipt_date_from;
    public string $receipt_date_to;
    public string $receipt_status = 'active';

    /**
     * @var array<int>
     */
    public array $selected_receipts = [];

    public string $entry_mode = 'unit';
    public int $bulk_quantity = 1;

    public int $quantity = 1;
    public ?string $cost_price = null;

    public bool $isSuperAdmin = false;

    public int $auth_user_id = 0;

    public ?string $notes = null;
    public int $selected_receipt_id = 0;

    public bool $show_receipt_modal = false;

    public bool $show_edit_modal = false;
    public bool $show_void_modal = false;

    public int $editing_receipt_id = 0;
    public int $edit_branch_id = 0;

    public array $edit_cart = [];

    public int $edit_product_id = 0;
    public string $edit_entry_mode = 'unit';
    public int $edit_bulk_quantity = 1;
    public int $edit_quantity = 1;
    public ?string $edit_cost_price = null;
    public ?string $edit_notes = null;

    public int $pending_void_receipt_id = 0;
    public ?string $void_reason = null;

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
        $this->auth_user_id = (int) ($user?->id ?? 0);

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        } else {
            $this->branch_id = 0;
        }

        $this->product_id = 0;
        $this->entry_mode = 'unit';
        $this->bulk_quantity = 1;
        $this->quantity = 1;
        $this->cost_price = null;
        $this->notes = null;
        $this->selected_receipt_id = 0;

        $this->product_search = '';
        $this->stock_search = '';
        $this->receipt_search = '';

        $today = Carbon::today();
        $this->receipt_date_from = $today->toDateString();
        $this->receipt_date_to = $today->toDateString();
        $this->receipt_status = 'active';
        $this->selected_receipts = [];

        $this->show_edit_modal = false;
        $this->show_void_modal = false;
        $this->editing_receipt_id = 0;
        $this->edit_branch_id = 0;
        $this->edit_cart = [];
        $this->edit_product_id = 0;
        $this->edit_entry_mode = 'unit';
        $this->edit_bulk_quantity = 1;
        $this->edit_quantity = 1;
        $this->edit_cost_price = null;
        $this->edit_notes = null;
        $this->pending_void_receipt_id = 0;
        $this->void_reason = null;
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;
            $this->selected_receipt_id = 0;
            $this->notes = null;
            $this->entry_mode = 'unit';
            $this->bulk_quantity = 1;
            $this->quantity = 1;
            $this->cost_price = null;
            $this->product_search = '';
            $this->stock_search = '';
            $this->receipt_search = '';

            $today = Carbon::today();
            $this->receipt_date_from = $today->toDateString();
            $this->receipt_date_to = $today->toDateString();
            $this->receipt_status = 'active';
            $this->selected_receipts = [];

            $this->show_edit_modal = false;
            $this->show_void_modal = false;
            $this->editing_receipt_id = 0;
            $this->edit_branch_id = 0;
            $this->edit_cart = [];
            $this->edit_product_id = 0;
            $this->edit_entry_mode = 'unit';
            $this->edit_bulk_quantity = 1;
            $this->edit_quantity = 1;
            $this->edit_cost_price = null;
            $this->edit_notes = null;
            $this->pending_void_receipt_id = 0;
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
            return;
        }

        $this->entry_mode = 'unit';
        $this->bulk_quantity = 1;
        $this->quantity = max(1, (int) $this->quantity);
    }

    public function updatedBranchId(): void
    {
        if (! $this->isSuperAdmin) {
            return;
        }

        $this->product_id = 0;
        $this->product_search = '';
        $this->entry_mode = 'unit';
        $this->bulk_quantity = 1;
        $this->quantity = 1;
    }

    public function save(): void
    {
        $data = $this->validate();

        $product = Product::query()
            ->with(['bulkType'])
            ->when((int) $data['branch_id'] > 0, fn ($q) => $q->where('branch_id', (int) $data['branch_id']))
            ->find((int) $data['product_id']);
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

    public function openReceiptModal(int $id): void
    {
        $this->selected_receipt_id = $id;
        $this->show_receipt_modal = true;
    }

    public function closeReceiptModal(): void
    {
        $this->show_receipt_modal = false;
    }

    public function openEditModal(int $id): void
    {
        $receipt = StockInReceipt::query()
            ->with(['items.product'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        if ($receipt->voided_at) {
            return;
        }

        $this->editing_receipt_id = (int) $receipt->id;
        $this->edit_branch_id = (int) $receipt->branch_id;
        $this->edit_notes = $receipt->notes;

        $this->edit_cart = [];
        foreach ($receipt->items as $item) {
            $this->edit_cart[(int) $item->product_id] = [
                'product_id' => (int) $item->product_id,
                'name' => (string) ($item->product?->name ?? '-'),
                'cost_price' => $item->cost_price !== null ? (string) $item->cost_price : null,
                'quantity' => (int) $item->quantity,
                'entry_mode' => (string) ($item->entry_mode ?? 'unit'),
                'bulk_quantity' => $item->bulk_quantity !== null ? (int) $item->bulk_quantity : null,
                'units_per_bulk' => $item->units_per_bulk !== null ? (int) $item->units_per_bulk : null,
                'bulk_type_id' => $item->bulk_type_id !== null ? (int) $item->bulk_type_id : null,
            ];
        }

        $this->edit_product_id = 0;
        $this->edit_entry_mode = 'unit';
        $this->edit_bulk_quantity = 1;
        $this->edit_quantity = 1;
        $this->edit_cost_price = null;

        $this->resetErrorBag();
        $this->show_edit_modal = true;
    }

    public function closeEditModal(): void
    {
        $this->show_edit_modal = false;
        $this->editing_receipt_id = 0;
        $this->edit_branch_id = 0;
        $this->edit_cart = [];
        $this->edit_product_id = 0;
        $this->edit_entry_mode = 'unit';
        $this->edit_bulk_quantity = 1;
        $this->edit_quantity = 1;
        $this->edit_cost_price = null;
        $this->edit_notes = null;
        $this->resetErrorBag();
    }

    public function updatedEditProductId(): void
    {
        $product = Product::query()
            ->with(['bulkType'])
            ->when($this->edit_branch_id > 0, fn ($q) => $q->where('branch_id', $this->edit_branch_id))
            ->find($this->edit_product_id);

        if ($product && (bool) $product->bulk_enabled) {
            $this->edit_entry_mode = 'bulk';
            $this->edit_bulk_quantity = max(1, (int) $this->edit_bulk_quantity);
            return;
        }

        $this->edit_entry_mode = 'unit';
        $this->edit_bulk_quantity = 1;
        $this->edit_quantity = max(1, (int) $this->edit_quantity);
    }

    public function addEditProduct(): void
    {
        $this->resetErrorBag('edit_cart');

        if ($this->editing_receipt_id <= 0 || $this->edit_branch_id <= 0 || $this->edit_product_id <= 0) {
            return;
        }

        $product = Product::query()
            ->with(['bulkType'])
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
            $unitsQty = max(1, (int) $this->edit_quantity);
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
            'cost_price' => $this->edit_cost_price,
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

    public function setEditCostPrice(int $productId, mixed $costPrice): void
    {
        $this->resetErrorBag('edit_cart');

        if (! isset($this->edit_cart[$productId])) {
            return;
        }

        $v = (float) $costPrice;
        if ($v < 0) {
            $v = 0;
        }

        $this->edit_cart[$productId]['cost_price'] = number_format($v, 2, '.', '');
    }

    public function removeEditItem(int $productId): void
    {
        unset($this->edit_cart[$productId]);
    }

    public function openVoidModal(int $id): void
    {
        $receipt = StockInReceipt::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        if ($receipt->voided_at) {
            return;
        }

        $this->pending_void_receipt_id = (int) $receipt->id;
        $this->void_reason = null;
        $this->resetErrorBag();
        $this->show_void_modal = true;
    }

    public function closeVoidModal(): void
    {
        $this->show_void_modal = false;
        $this->pending_void_receipt_id = 0;
        $this->void_reason = null;
        $this->resetErrorBag();
    }

    public function confirmVoidReceipt(): void
    {
        $this->resetErrorBag();

        if ($this->pending_void_receipt_id <= 0) {
            return;
        }

        try {
            DB::transaction(function () {
                $receipt = StockInReceipt::query()
                    ->whereKey($this->pending_void_receipt_id)
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
                    $afterStock = $beforeStock - (int) $item->quantity;

                    if ($afterStock < 0) {
                        throw ValidationException::withMessages([
                            'void_reason' => 'Cannot void receipt: stock would go negative for product #' . (int) $item->product_id . '.',
                        ]);
                    }

                    $stock->current_stock = $afterStock;
                    $stock->save();

                    StockMovement::query()->create([
                        'branch_id' => (int) $receipt->branch_id,
                        'product_id' => (int) $item->product_id,
                        'user_id' => auth()->id(),
                        'movement_type' => 'OUT',
                        'quantity' => (int) $item->quantity,
                        'before_stock' => $beforeStock,
                        'after_stock' => (int) $stock->current_stock,
                        'unit_cost' => $item->cost_price !== null ? (string) $item->cost_price : null,
                        'unit_price' => null,
                        'stock_in_receipt_id' => (int) $receipt->id,
                        'sales_receipt_id' => null,
                        'moved_at' => now(),
                        'notes' => 'STOCK IN VOID',
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
        session()->flash('status', 'Receipt voided successfully.');
    }

    public function saveEdit(): void
    {
        $this->resetErrorBag();

        if ($this->editing_receipt_id <= 0) {
            return;
        }

        $items = array_values($this->edit_cart);
        if (count($items) === 0) {
            $this->addError('edit_cart', 'Cart is empty.');
            return;
        }

        try {
            DB::transaction(function () use ($items) {
                $receipt = StockInReceipt::query()
                    ->whereKey($this->editing_receipt_id)
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
                    $afterStock = $beforeStock - (int) $oldItem->quantity;
                    if ($afterStock < 0) {
                        throw ValidationException::withMessages([
                            'edit_cart' => 'Cannot edit receipt: reversing old quantities would make stock negative for product #' . (int) $oldItem->product_id . '.',
                        ]);
                    }

                    $stock->current_stock = $afterStock;
                    $stock->save();

                    StockMovement::query()->create([
                        'branch_id' => (int) $receipt->branch_id,
                        'product_id' => (int) $oldItem->product_id,
                        'user_id' => auth()->id(),
                        'movement_type' => 'OUT',
                        'quantity' => (int) $oldItem->quantity,
                        'before_stock' => $beforeStock,
                        'after_stock' => (int) $stock->current_stock,
                        'unit_cost' => $oldItem->cost_price !== null ? (string) $oldItem->cost_price : null,
                        'unit_price' => null,
                        'stock_in_receipt_id' => (int) $receipt->id,
                        'sales_receipt_id' => null,
                        'moved_at' => now(),
                        'notes' => 'STOCK IN EDIT REVERSAL',
                    ]);
                }

                StockInItem::query()->where('stock_in_receipt_id', (int) $receipt->id)->delete();

                $totalQty = 0;
                $totalCost = 0.0;

                foreach ($items as $item) {
                    $qty = (int) $item['quantity'];
                    $totalQty += $qty;

                    $stock = ProductStock::query()->firstOrCreate(
                        ['branch_id' => (int) $receipt->branch_id, 'product_id' => (int) $item['product_id']],
                        ['current_stock' => 0, 'minimum_stock' => 0, 'cost_price' => null]
                    );

                    $stock = ProductStock::query()->whereKey($stock->id)->lockForUpdate()->firstOrFail();
                    $beforeStock = (int) $stock->current_stock;
                    $stock->current_stock = $beforeStock + $qty;

                    $beforeCost = $stock->cost_price !== null ? (float) $stock->cost_price : null;
                    $incomingCost = ($item['cost_price'] !== null && $item['cost_price'] !== '') ? (float) $item['cost_price'] : $beforeCost;

                    if ($incomingCost !== null) {
                        $afterQty = $beforeStock + $qty;
                        if ($afterQty > 0) {
                            $beforeCostValue = $beforeCost !== null ? (float) $beforeCost : (float) $incomingCost;
                            $weighted = (($beforeStock * $beforeCostValue) + ($qty * $incomingCost)) / $afterQty;
                            $stock->cost_price = number_format($weighted, 2, '.', '');
                        }
                    }

                    $stock->save();

                    $afterStock = (int) $stock->current_stock;
                    $lineTotal = ($item['cost_price'] !== null && $item['cost_price'] !== '') ? ((float) $item['cost_price'] * $qty) : null;
                    if ($lineTotal !== null) {
                        $totalCost += $lineTotal;
                    }

                    StockInItem::query()->create([
                        'stock_in_receipt_id' => (int) $receipt->id,
                        'product_id' => (int) $item['product_id'],
                        'entry_mode' => (string) ($item['entry_mode'] ?? 'unit'),
                        'bulk_quantity' => $item['bulk_quantity'] ?? null,
                        'units_per_bulk' => $item['units_per_bulk'] ?? null,
                        'bulk_type_id' => $item['bulk_type_id'] ?? null,
                        'quantity' => $qty,
                        'cost_price' => ($item['cost_price'] !== null && $item['cost_price'] !== '') ? (string) $item['cost_price'] : null,
                        'line_total' => $lineTotal !== null ? number_format($lineTotal, 2, '.', '') : null,
                    ]);

                    StockMovement::query()->create([
                        'branch_id' => (int) $receipt->branch_id,
                        'product_id' => (int) $item['product_id'],
                        'user_id' => auth()->id(),
                        'movement_type' => 'IN',
                        'quantity' => $qty,
                        'before_stock' => $beforeStock,
                        'after_stock' => $afterStock,
                        'unit_cost' => ($item['cost_price'] !== null && $item['cost_price'] !== '') ? (string) $item['cost_price'] : null,
                        'unit_price' => null,
                        'stock_in_receipt_id' => (int) $receipt->id,
                        'sales_receipt_id' => null,
                        'moved_at' => now(),
                        'notes' => 'STOCK IN EDIT',
                    ]);
                }

                $receipt->notes = $this->edit_notes;
                $receipt->total_quantity = $totalQty;
                $receipt->total_cost = $totalCost > 0 ? number_format($totalCost, 2, '.', '') : null;
                $receipt->save();
            });
        } catch (ValidationException $e) {
            $this->setErrorBag($e->validator->getMessageBag());
            return;
        }

        $this->closeEditModal();
        session()->flash('status', 'Receipt updated successfully.');
    }

    public function clearSelectedReceipts(): void
    {
        $this->selected_receipts = [];
    }

    public function selectAllReceiptsForDay(string $day): void
    {
        $from = Carbon::parse($day)->startOfDay();
        $to = Carbon::parse($day)->endOfDay();

        $q = StockInReceipt::query()
            ->whereBetween('received_at', [$from, $to])
            ->when(! $this->isSuperAdmin, fn ($qq) => $qq->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($qq) => $qq->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id <= 0, fn ($qq) => $qq->whereRaw('1 = 0'));

        if ($this->receipt_status === 'active') {
            $q->whereNull('voided_at');
        } elseif ($this->receipt_status === 'voided') {
            $q->whereNotNull('voided_at');
        }

        if (trim($this->receipt_search) !== '') {
            $term = '%' . trim($this->receipt_search) . '%';
            $q->where(function ($qq) use ($term) {
                $qq->where('receipt_no', 'like', $term)
                    ->orWhereHas('branch', fn ($qb) => $qb->where('name', 'like', $term))
                    ->orWhereHas('user', fn ($qu) => $qu->where('name', 'like', $term));
            });
        }

        $this->selected_receipts = $q->orderByDesc('received_at')->pluck('id')->map(fn ($v) => (int) $v)->all();
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

        $stocks = ProductStock::query()
            ->with(['product'])
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id <= 0, fn ($q) => $q->whereRaw('1 = 0'))
            ->join('products', 'products.id', '=', 'product_stocks.product_id')
            ->when(trim($this->stock_search) !== '', function ($q) {
                $term = '%' . trim($this->stock_search) . '%';
                $q->where('products.name', 'like', $term);
            })
            ->orderBy('products.name')
            ->select('product_stocks.*')
            ->get();

        $receipts = StockInReceipt::query()
            ->with(['branch', 'user'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id <= 0, fn ($q) => $q->whereRaw('1 = 0'))
            ->when(trim($this->receipt_search) !== '', function ($q) {
                $term = '%' . trim($this->receipt_search) . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('receipt_no', 'like', $term)
                        ->orWhereHas('branch', fn ($qb) => $qb->where('name', 'like', $term))
                        ->orWhereHas('user', fn ($qu) => $qu->where('name', 'like', $term));
                });
            })
            ->when($this->receipt_date_from !== '' && $this->receipt_date_to !== '', function ($q) {
                $from = Carbon::parse($this->receipt_date_from)->startOfDay();
                $to = Carbon::parse($this->receipt_date_to)->endOfDay();
                $q->whereBetween('received_at', [$from, $to]);
            })
            ->when($this->receipt_status === 'active', fn ($q) => $q->whereNull('voided_at'))
            ->when($this->receipt_status === 'voided', fn ($q) => $q->whereNotNull('voided_at'))
            ->orderByDesc('received_at')
            ->limit(300)
            ->get();

        $selectedReceipt = null;
        if ($this->selected_receipt_id > 0) {
            $selectedReceipt = StockInReceipt::query()
                ->with(['branch', 'user', 'items.product'])
                ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
                ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
                ->find($this->selected_receipt_id);
        }

        $editProducts = collect();
        if ($this->show_edit_modal && $this->edit_branch_id > 0) {
            $editProducts = Product::query()
                ->with(['bulkType.bulkUnit'])
                ->where('branch_id', (int) $this->edit_branch_id)
                ->orderBy('name')
                ->get();
        }

        return view('livewire.stock-in-index', [
            'branches' => $branches,
            'products' => $products,
            'selectedProduct' => $selectedProduct,
            'stocks' => $stocks,
            'receipts' => $receipts,
            'selectedReceipt' => $selectedReceipt,
            'isSuperAdmin' => $this->isSuperAdmin,
            'editProducts' => $editProducts,
        ]);
    }
}
