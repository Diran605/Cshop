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

        <div class="space-y-6">
            {{-- Top Section: Config and Payment Details --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="text-sm font-medium text-slate-700 mb-4">{{ __('Sale Configuration') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="ui-label">{{ __('Sale Date') }}</label>
                            <input type="date" wire:model.defer="sold_at_date" class="mt-1 ui-input" />
                            @error('sold_at_date') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                        @if ($isSuperAdmin)
                        <div>
                            <label class="ui-label">{{ __('Branch') }}</label>
                            <select wire:model.live="branch_id" class="mt-1 ui-select">
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

            {{-- Bottom Section: Items and Cart --}}
            <div class="space-y-6">
                <div class="ui-card">
                    <div class="ui-card-body space-y-6">
                        <!-- Add Product -->
                        <div class="bg-slate-50 rounded-lg p-4">
                            <div class="text-sm font-medium text-slate-700 mb-3">{{ __('Add Product') }}</div>
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                                <div class="md:col-span-12">
                                    <label class="ui-label mb-1">{{ __('Product') }}</label>
                                    <livewire:sales-batch-selector :branch-id="$branch_id" />
                                </div>
                                <div class="md:col-span-2">
                                    <label class="ui-label">{{ __('Mode') }}</label>
                                    <select wire:model.live="entry_mode" class="mt-1 ui-select" @if (!($selected_product_data && $selected_product_data['bulk_enabled'])) disabled @endif>
                                        <option value="unit">{{ __('Unit') }}</option>
                                        <option value="bulk">{{ __('Bulk') }}</option>
                                    </select>
                                    @if ($entry_mode === 'bulk' && $selected_product_data && $selected_product_data['bulk_enabled'])
                                    <div class="text-xs text-green-600 mt-1 truncate">
                                        {{ $selected_product_data['units_per_bulk'] }} units/bulk
                                    </div>
                                    @endif
                                </div>
                                <div class="md:col-span-1">
                                    <label class="ui-label">{{ __('Qty') }}</label>
                                    @if ($entry_mode === 'bulk' && $selected_product_data && $selected_product_data['bulk_enabled'])
                                    <input type="number" wire:model.defer="bulk_quantity" min="1" class="mt-1 ui-input text-center px-1" />
                                    @else
                                    <input type="number" wire:model.defer="entry_quantity" min="1" class="mt-1 ui-input text-center px-1" />
                                    @endif
                                </div>
                                <div class="md:col-span-6">
                                    <label class="ui-label">
                                        @if ($entry_mode === 'bulk' && $selected_product_data && $selected_product_data['bulk_enabled'])
                                        {{ __('Price per Bulk (opt)') }}
                                        @else
                                        {{ __('Price per Unit (opt)') }}
                                        @endif
                                    </label>
                                    <input type="number" min="0" step="0.01" class="mt-1 ui-input" wire:model.live="custom_entry_price" placeholder="{{ $entryPriceDisplay }}" @if ($product_id <= 0) disabled @endif />
                                </div>
                                <div class="md:col-span-3 flex items-start mt-[28px]">
                                    <button type="button" wire:click="addProduct" class="w-full ui-btn-primary h-[42px]">
                                        {{ __('Add') }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Items -->
                        <div>
                            <div class="text-sm font-medium text-slate-700 mb-2">{{ __('Items') }}</div>
                            @if (count($cartItems) > 0)
                                <div class="overflow-x-auto">
                                    <table class="ui-table text-sm">
                                        <thead>
                                            <tr>
                                                <th class="text-center">{{ __('Product') }}</th>
                                                <th class="text-center">{{ __('Qty') }}</th>
                                                <th class="text-center">{{ __('Price') }}</th>
                                                <th class="text-center">{{ __('Total') }}</th>
                                                <th class="text-center"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($cartItems as $item)
                                                @php
                                                    $lineTotal = (float) $item['unit_price'] * (int) $item['quantity'];
                                                @endphp
                                                <tr wire:key="cart-item-{{ $item['product_id'] }}">
                                                    <td class="text-center">
                                                        <div class="font-medium text-slate-900">{{ $item['name'] }}</div>
                                                        @if (isset($item['is_clearance']) && $item['is_clearance'])
                                                            <div class="mt-1">
                                                                <button type="button" wire:click="toggleClearancePrice({{ $item['product_id'] }})"
                                                                    class="text-xs px-2 py-0.5 rounded {{ ($item['use_clearance_price'] ?? true) ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                                                                    {{ ($item['use_clearance_price'] ?? true) ? __('Clearance Price') : __('Normal Price') }}
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" min="1" step="1" class="w-16 text-center ui-input py-1 px-2 mx-auto"
                                                            value="{{ $item['quantity'] }}"
                                                            wire:change="setQuantity({{ $item['product_id'] }}, $event.target.value)" />
                                                    </td>
                                                    <td class="text-center">
                                                        <input type="number" min="0" step="0.01" class="w-24 text-center ui-input py-1 px-2 mx-auto"
                                                            value="{{ $item['unit_price'] }}"
                                                            wire:change="setUnitPrice({{ $item['product_id'] }}, $event.target.value)" />
                                                    </td>
                                                    <td class="text-center font-mono font-semibold text-green-700">
                                                        {{ number_format($lineTotal, 2) }}
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" wire:click="removeItem({{ $item['product_id'] }})" class="text-red-500 hover:text-red-700">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-right font-medium text-slate-700">{{ __('Sub Total') }}</td>
                                                <td class="text-right font-mono font-medium">{{ number_format((float) $subTotal, 2) }}</td>
                                                <td></td>
                                            </tr>
                                            <tr class="bg-slate-50">
                                                <td colspan="3" class="text-right font-semibold text-slate-900 py-3">{{ __('Grand Total') }}</td>
                                                <td class="text-right font-mono font-bold text-lg text-purple-700 py-3">{{ number_format((float) $grandTotal, 2) }}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-sm text-slate-500 py-8 text-center border border-dashed border-slate-300 rounded-lg">
                                    {{ __('No items in cart.') }}
                                </div>
                            @endif

                            @error('cart')
                            <div class="mt-4 rounded-md bg-red-50 p-4 text-sm text-red-800 border border-red-200">{{ $message }}</div>
                            @enderror

                            {{-- Payment and Customer Details (Moved from top) --}}
                            <div class="mt-8 pt-6 border-t border-slate-200">
                                <h4 class="text-sm font-semibold text-slate-800 mb-4">{{ __('Checkout Details') }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="ui-label">{{ __('Customer Name (optional)') }}</label>
                                        <input type="text" wire:model.defer="customer_name" class="mt-1 ui-input" />
                                        @error('customer_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                    </div>
                                    <div>
                                        <label class="ui-label">{{ __('Payment Method') }}</label>
                                        <div class="mt-1 p-2 bg-green-50 border border-green-200 rounded-md flex items-center justify-center h-[42px]">
                                            <span class="text-green-800 text-sm font-medium">{{ __('Cash') }}</span>
                                        </div>
                                    </div>
                                    <div class="md:col-span-2">
                                        <label class="ui-label">{{ __('Notes (optional)') }}</label>
                                        <textarea wire:model.defer="notes" rows="2" class="mt-1 ui-input" placeholder="Any additional notes about this sale"></textarea>
                                        @error('notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-slate-200">
                                <button type="button" wire:click="clearCart" class="ui-btn-secondary" @if (count($cartItems) === 0) disabled @endif>
                                    {{ __('Clear') }}
                                </button>
                                <button type="button" wire:click="finalizeSale" class="ui-btn-primary px-8" @if (count($cartItems) === 0) disabled @endif>
                                    {{ __('Post Sale') }}
                                </button>
                            </div>
                        </div>
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

                    <div>
                        <label class="ui-label">{{ __('Edit Reason') }} <span class="text-red-500">*</span></label>
                        <textarea wire:model.defer="edit_reason" rows="2" class="mt-1 ui-input" placeholder="{{ __('Why are you editing this sale?') }}"></textarea>
                        @error('edit_reason') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
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