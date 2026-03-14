<div class="ui-page">
    <div class="ui-page-container">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-bold tracking-wider uppercase text-purple-600 mb-1">Point of Sale</div>
                    <h1 class="text-2xl font-bold text-slate-900">Record Sale</h1>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="ui-alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="ui-alert-warning">
                {{ session('warning') }}
            </div>
        @endif

        @if (session('error'))
            <div class="ui-alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- Row 1: Sale Configuration (left) + Add Products (right) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Sale Configuration Card --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Sale Configuration</h3>
                    <div class="space-y-4">
                        {{-- Sale Type Toggle --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Sale Type</label>
                            <div class="inline-flex rounded-lg border border-slate-300/80 bg-white/60 p-1">
                                <button type="button"
                                    wire:click="$set('sale_entry_type', 'single')"
                                    class="px-4 py-2 text-sm font-medium rounded-md transition-all {{ $sale_entry_type === 'single' ? 'bg-purple-500 text-white shadow-md' : 'text-slate-700 hover:bg-slate-100' }}">
                                    Single
                                </button>
                                <button type="button"
                                    wire:click="$set('sale_entry_type', 'group')"
                                    class="px-4 py-2 text-sm font-medium rounded-md transition-all {{ $sale_entry_type === 'group' ? 'bg-purple-500 text-white shadow-md' : 'text-slate-700 hover:bg-slate-100' }}">
                                    Group
                                </button>
                            </div>
                        </div>

                        {{-- Sale Date --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Sale Date</label>
                            <input type="date" wire:model.defer="sold_at_date" class="ui-input" />
                            @error('sold_at_date') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        {{-- Branch Selection for Super Admin --}}
                        @if ($isSuperAdmin)
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Branch</label>
                                <select wire:model.live="branch_id" class="ui-select">
                                    <option value="0">{{ __('Select...') }}</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Add Products Card --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Add Products</h3>

                    <div class="space-y-4">
                        {{-- Product Dropdown --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Product</label>
                            <input type="text" wire:model.live.debounce.300ms="product_search" class="ui-input mb-2" placeholder="Search product..." autocomplete="off" />
                            @if (count($searchableProducts) > 0)
                                <div class="border border-slate-300 rounded-md max-h-48 overflow-y-auto absolute z-10 bg-white w-full max-w-md">
                                    @foreach ($searchableProducts as $product)
                                        <button type="button" wire:click="selectProduct({{ $product->id }})" class="w-full text-left px-3 py-2 hover:bg-slate-100 border-b border-slate-100 last:border-b-0">
                                            <div class="font-medium">{{ $product->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $product->category?->name }}</div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            @if ($selectedProduct)
                                <div class="mt-2 p-2 bg-green-50 border border-green-200 rounded-md">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-green-800">{{ $selectedProduct->name }}</span>
                                            <span class="text-xs text-green-600 ml-2">{{ $selectedProduct->category?->name }}</span>
                                        </div>
                                        <button type="button" wire:click="$set('product_id', 0)" class="text-green-700 hover:text-green-900 text-sm">Clear</button>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Entry Type Toggle --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Entry Type</label>
                            <div class="inline-flex rounded-lg border border-slate-300/80 bg-white/60 p-1">
                                <button type="button" wire:click="$set('entry_mode', 'unit')" class="px-4 py-2 text-sm font-medium rounded-md transition-all {{ $entry_mode === 'unit' ? 'bg-purple-500 text-white shadow-md' : 'text-slate-700 hover:bg-slate-100' }}" @if (!($selected_product_data && $selected_product_data['bulk_enabled'])) disabled @endif>
                                    Units
                                </button>
                                <button type="button" wire:click="$set('entry_mode', 'bulk')" class="px-4 py-2 text-sm font-medium rounded-md transition-all {{ $entry_mode === 'bulk' ? 'bg-purple-500 text-white shadow-md' : 'text-slate-700 hover:bg-slate-100' }}" @if (!($selected_product_data && $selected_product_data['bulk_enabled'])) disabled @endif>
                                    Bulk
                                </button>
                            </div>
                        </div>

                        {{-- Quantity and Price --}}
                        <div class="grid grid-cols-2 gap-4">
                            @if ($entry_mode === 'bulk' && $selected_product_data && $selected_product_data['bulk_enabled'])
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Bulk Quantity</label>
                                    <input type="number" min="1" wire:model.defer="bulk_quantity" class="ui-input font-mono" />
                                </div>
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Quantity</label>
                                    <input type="number" min="1" wire:model.defer="entry_quantity" class="ui-input font-mono" />
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    @if ($entry_mode === 'bulk' && $selected_product_data && $selected_product_data['bulk_enabled'])
                                        Price per Bulk
                                    @else
                                        Price per Unit
                                    @endif
                                </label>
                                <input wire:key="price-{{ $product_id }}-{{ $entry_mode }}" type="number" min="0" step="0.01" class="ui-input font-mono" value="{{ $entryPriceDisplay }}" wire:change="setUnitPrice({{ (int) $product_id }}, $event.target.value)" @if ($product_id <= 0) disabled @endif />
                            </div>
                        </div>

                        {{-- Add Item Button --}}
                        <button type="button" wire:click="addProduct" class="w-full bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-medium py-3 px-4 rounded-lg shadow-md transition-all">
                            + Add Item
                        </button>

                        {{-- Items List --}}
                        @if (count($cartItems) > 0)
                            <div class="border-t border-slate-200 pt-4 space-y-3">
                                @foreach ($cartItems as $item)
                                    @php($available = $stockMap[$item['product_id']] ?? 0)
                                    <div class="flex items-start justify-between p-3 bg-slate-50 rounded-lg">
                                        <div class="flex-1 min-w-0">
                                            <div class="font-medium text-slate-900 truncate">
                                                {{ $item['name'] }}
                                                @if (isset($item['is_clearance']) && $item['is_clearance'])
                                                    <span class="ml-1 px-1.5 py-0.5 bg-yellow-100 text-yellow-800 text-xs rounded">Clearance</span>
                                                @endif
                                            </div>
                                            <div class="mt-1 text-sm text-slate-600 flex items-center gap-2">
                                                {{ $item['quantity'] }} × 
                                                <input type="number" min="0" step="0.01" class="w-20 px-1 py-0.5 text-sm border border-slate-300 rounded" 
                                                    value="{{ $item['unit_price'] }}" 
                                                    wire:change="setUnitPrice({{ $item['product_id'] }}, $event.target.value)" />
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3 ml-4">
                                            <div class="font-mono font-bold text-purple-600">
                                                {{ number_format((float) $item['unit_price'] * (int) $item['quantity'], 2) }}
                                            </div>
                                            <button type="button" wire:click="removeItem({{ $item['product_id'] }})" class="text-red-500 hover:text-red-700 font-bold text-lg">×</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Payment Details (left) + Order Summary (right) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            {{-- Payment Details Card --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Payment Details</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Customer Name (optional)</label>
                            <input type="text" wire:model.defer="customer_name" class="ui-input" />
                            @error('customer_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Payment</label>
                            <div class="p-2 bg-green-50 border border-green-200 rounded-md">
                                <span class="text-green-800 font-medium">Cash</span>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Notes (optional)</label>
                            <textarea wire:model.defer="notes" rows="3" class="ui-input"></textarea>
                            @error('notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Order Summary Card --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Order Summary</h3>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-slate-200">
                            <span class="text-slate-600">Sub Total</span>
                            <span class="font-medium text-purple-600">{{ number_format((float) $subTotal, 2) }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2">
                            <span class="text-slate-600">Grand Total</span>
                            <span class="text-2xl font-bold text-purple-600 font-mono">{{ number_format((float) $grandTotal, 2) }}</span>
                        </div>
                    </div>

                    @error('cart')
                        <div class="mt-4 rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $message }}</div>
                    @enderror

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-4 mt-6 pt-4 border-t border-slate-200">
                        <button type="button" wire:click="clearCart" class="flex-1 border-2 border-red-500 text-red-500 font-medium py-3 px-6 rounded-lg hover:bg-red-50 transition-all">
                            Clear Items
                        </button>
                        <button type="button" wire:click="finalizeSale" class="flex-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-all">
                            Post Sale
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if ($show_sale_modal && $selectedSale)
            <div wire:key="sale-modal-{{ $selectedSale->id }}" class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeSaleModal" data-modal-overlay></div>
                <div class="relative w-full max-w-3xl ui-card">
                    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Sale Details') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ $selectedSale->receipt_no }}</div>
                            <div class="mt-1 text-sm text-slate-600">
                                {{ $selectedSale->branch?->name ?? '-' }}
                                @if ($selectedSale->user)
                                    {{ '• ' . $selectedSale->user->name }}
                                @endif
                                {{ '• ' . $selectedSale->sold_at?->format('Y-m-d H:i') }}
                            </div>
                            @if ($selectedSale->customer_name)
                                <div class="mt-1 text-sm text-slate-600">
                                    {{ __('Customer:') }} {{ $selectedSale->customer_name }}
                                </div>
                            @endif
                        </div>

                        <button type="button" wire:click="closeSaleModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                    </div>

                    <div class="p-4">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                            <div class="text-sm text-slate-700">
                                {{ __('Payment:') }}
                                <span class="font-medium">{{ strtoupper($selectedSale->payment_method) }}</span>
                            </div>
                            <a href="{{ route('sales.print', $selectedSale->id) }}" target="_blank" class="ui-btn-secondary w-full sm:w-auto text-center">
                                {{ __('Print Receipt') }}
                            </a>
                        </div>

                        @if ($selectedSale->notes)
                            <div class="mt-2 text-sm text-slate-700">{{ $selectedSale->notes }}</div>
                        @endif

                        <div class="mt-4 overflow-x-auto">
                            <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Qty') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedSale->items as $item)
                                        <tr wire:key="sale-modal-item-{{ $item->id }}">
                                            <td>{{ $item->product?->name ?? '-' }}</td>
                                            <td>
                                                @if ((string) $item->entry_mode === 'bulk')
                                                    {{ (int) ($item->bulk_quantity ?? 0) }} {{ __('bulk') }}
                                                    <span class="text-xs text-slate-500">({{ (int) $item->quantity }} {{ __('units') }})</span>
                                                @else
                                                    {{ (int) $item->quantity }}
                                                @endif
                                            </td>
                                            <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                                            <td>{{ number_format((float) $item->line_total, 2) }}</td>
                                        </tr>
                                    @endforeach

                                    @if ($selectedSale->items->isEmpty())
                                        <tr>
                                            <td colspan="4" class="ui-table-empty">{{ __('No items found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            </div>
                        </div>

                        <div class="mt-4 ui-muted-panel space-y-1">
                            <div class="flex items-center justify-between">
                                <div>{{ __('Grand Total') }}</div>
                                <div class="font-semibold text-slate-900">{{ number_format((float) $selectedSale->grand_total, 2) }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>{{ __('COGS') }}</div>
                                <div class="font-medium">{{ number_format((float) $selectedSale->cogs_total, 2) }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>{{ __('Profit') }}</div>
                                <div class="font-medium">{{ number_format((float) $selectedSale->profit_total, 2) }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>{{ __('Paid') }}</div>
                                <div class="font-medium">{{ number_format((float) $selectedSale->amount_paid, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($show_edit_modal)
            <div wire:key="edit-modal-{{ $editing_sale_id }}" class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeEditModal" data-modal-overlay></div>
                <div class="relative w-full max-w-5xl ui-card">
                    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Edit Sale') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ __('Full Edit') }}</div>
                        </div>
                        <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                    </div>

                    <div class="p-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="ui-label">{{ __('Product') }}</label>
                                <select wire:model="edit_product_id" class="mt-1 ui-select">
                                    <option value="0">{{ __('Select...') }}</option>
                                    @foreach ($editProducts as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Entry Type') }}</label>
                                <div class="mt-1 inline-flex rounded-lg border border-slate-300/80 bg-white/60 p-1">
                                    <button type="button" wire:click="$set('edit_entry_mode', 'unit')" class="px-3 py-2 text-sm font-medium rounded-md {{ $edit_entry_mode === 'unit' ? 'bg-primary-blue text-white' : 'text-slate-700 hover:bg-soft-blue' }}">
                                        {{ __('Units') }}
                                    </button>
                                    <button type="button" wire:click="$set('edit_entry_mode', 'bulk')" class="px-3 py-2 text-sm font-medium rounded-md {{ $edit_entry_mode === 'bulk' ? 'bg-primary-blue text-white' : 'text-slate-700 hover:bg-soft-blue' }}">
                                        {{ __('Bulk') }}
                                    </button>
                                </div>
                            </div>

                            <div>
                                @if ($edit_entry_mode === 'bulk')
                                    <label class="ui-label">{{ __('Bulk Quantity') }}</label>
                                    <input type="number" min="1" wire:model.defer="edit_bulk_quantity" class="mt-1 ui-input" />
                                @else
                                    <label class="ui-label">{{ __('Quantity (Units)') }}</label>
                                    <input type="number" min="1" wire:model.defer="edit_entry_quantity" class="mt-1 ui-input" />
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="button" wire:click="addEditProduct" class="ui-btn-primary w-full sm:w-auto">{{ __('Add') }}</button>
                        </div>

                        @error('edit_cart')
                            <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $message }}</div>
                        @enderror

                        <div class="overflow-x-auto">
                            <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Available') }}</th>
                                        <th>{{ __('Qty') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Total') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($editCartItems as $item)
                                        @php($available = $editStockMap[$item['product_id']] ?? 0)
                                        <tr wire:key="edit-cart-{{ $item['product_id'] }}">
                                            <td>
                                                <div class="font-medium">{{ $item['name'] }}</div>
                                            </td>
                                            <td class="{{ $available < (int) $item['quantity'] ? 'text-red-700' : '' }}">
                                                {{ $available }}
                                            </td>
                                            <td>
                                                <div class="inline-flex items-center gap-2">
                                                    <button type="button" wire:click="decrementEditItem({{ $item['product_id'] }})" class="ui-stepper-btn">-</button>
                                                    <input type="number" min="1" class="w-20 ui-input-compact" value="{{ (string) ($item['entry_mode'] ?? 'unit') === 'bulk' ? (int) ($item['bulk_quantity'] ?? 0) : (int) $item['quantity'] }}" wire:change="setEditQuantity({{ $item['product_id'] }}, $event.target.value)" />
                                                    <button type="button" wire:click="incrementEditItem({{ $item['product_id'] }})" class="ui-stepper-btn">+</button>
                                                </div>
                                                @if ((string) ($item['entry_mode'] ?? 'unit') === 'bulk')
                                                    <div class="mt-1 text-xs text-slate-500">{{ __('Units:') }} {{ (int) $item['quantity'] }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @php($isBulk = (string) ($item['entry_mode'] ?? 'unit') === 'bulk')
                                                @php($unitsPerBulk = (int) ($item['units_per_bulk'] ?? 0))
                                                @php($displayPrice = $isBulk && $unitsPerBulk > 0 ? number_format(((float) $item['unit_price'] * $unitsPerBulk), 2, '.', '') : (string) $item['unit_price'])
                                                <input type="number" min="0" step="0.01" class="w-28 ui-input-compact" value="{{ $displayPrice }}" wire:change="setEditUnitPrice({{ $item['product_id'] }}, $event.target.value)" />
                                            </td>
                                            <td>
                                                {{ number_format((float) $item['unit_price'] * (int) $item['quantity'], 2) }}
                                            </td>
                                            <td class="text-right">
                                                <button type="button" wire:click="removeEditItem({{ $item['product_id'] }})" class="ui-btn-link-danger">{{ __('Remove') }}</button>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if (count($editCartItems) === 0)
                                        <tr>
                                            <td colspan="6" class="ui-table-empty">{{ __('Cart is empty.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Customer Name (optional)') }}</label>
                                <input type="text" wire:model.defer="edit_customer_name" class="mt-1 ui-input" />
                                @error('edit_customer_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="ui-label">{{ __('Method') }}</label>
                                <div class="mt-1 rounded-lg border border-slate-300/80 bg-white/60 px-3 py-2 text-sm text-slate-700">
                                    {{ __('Cash') }}
                                </div>
                            </div>
                            <div>
                                <label class="ui-label">{{ __('Amount Paid') }}</label>
                                <input type="number" min="0" step="0.01" wire:model.defer="edit_amount_paid" class="mt-1 ui-input" />
                                @error('edit_amount_paid') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Notes (optional)') }}</label>
                            <textarea wire:model.defer="edit_notes" rows="2" class="mt-1 ui-input"></textarea>
                        </div>

                        <div class="ui-muted-panel space-y-1">
                            <div class="flex items-center justify-between">
                                <div>{{ __('Sub Total') }}</div>
                                <div class="font-medium">{{ number_format((float) $editSubTotal, 2) }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>{{ __('Grand Total') }}</div>
                                <div class="font-semibold text-slate-900">{{ number_format((float) $editGrandTotal, 2) }}</div>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                            <button type="button" wire:click="closeEditModal" class="ui-btn-secondary w-full sm:w-auto">{{ __('Cancel') }}</button>
                            <button type="button" wire:click="saveEdit" class="ui-btn-primary w-full sm:w-auto">{{ __('Save Changes') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($show_void_modal)
            <div wire:key="void-modal-{{ $pending_void_sale_id }}" class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeVoidModal" data-modal-overlay></div>
                <div class="relative w-full max-w-lg ui-card">
                    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Void Sale') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ __('Confirm Void') }}</div>
                        </div>
                        <button type="button" wire:click="closeVoidModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                    </div>
                    <div class="p-4 space-y-4">
                        <div class="text-sm text-slate-700">
                            {{ __('This will reverse stock for all items and mark the receipt as voided.') }}
                        </div>
                        <div>
                            <label class="ui-label">{{ __('Reason (optional)') }}</label>
                            <textarea wire:model.defer="void_reason" rows="2" class="mt-1 ui-input"></textarea>
                        </div>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                            <button type="button" wire:click="closeVoidModal" class="ui-btn-secondary w-full sm:w-auto">{{ __('Cancel') }}</button>
                            <button type="button" wire:click="confirmVoidSale" class="ui-btn-danger w-full sm:w-auto">{{ __('Void') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
