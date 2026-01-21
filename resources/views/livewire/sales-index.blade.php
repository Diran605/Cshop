<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Sales') }}
            </h2>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Point of Sale') }}</h3>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Branch') }}</label>
                            @if ($isSuperAdmin)
                                <select wire:model="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="0">{{ __('Select...') }}</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            @else
                                <div class="mt-1 rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                    {{ $branches->first()?->name ?? '-' }}
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Product') }}</label>
                            <div class="mt-1 flex gap-2">
                                <select wire:model="product_id" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="0">{{ __('Select...') }}</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Entry Type') }}</label>
                                <select wire:model="entry_mode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @if (!($selectedProduct && (bool) $selectedProduct->bulk_enabled)) disabled @endif>
                                    <option value="unit">{{ __('Units') }}</option>
                                    @if ($selectedProduct && (bool) $selectedProduct->bulk_enabled)
                                        <option value="bulk">{{ __('Bulk') }}</option>
                                    @endif
                                </select>
                            </div>

                            <div class="sm:col-span-2">
                                @if (($selectedProduct && (bool) $selectedProduct->bulk_enabled) && $entry_mode === 'bulk')
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Bulk Quantity') }}</label>
                                    <input type="number" min="1" wire:model.defer="bulk_quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    <div class="mt-1 text-xs text-gray-500">
                                        {{ __('Units per bulk:') }}
                                        <span class="font-medium">
                                            {{ (int) ($selectedProduct?->bulkType?->units_per_bulk ?? 0) }}
                                            {{ $selectedProduct?->bulkType?->bulkUnit?->name ? '(' . $selectedProduct->bulkType->bulkUnit->name . ')' : '' }}
                                        </span>
                                        {{ __('• Total units:') }}
                                        <span class="font-medium">{{ (int) $bulk_quantity * (int) ($selectedProduct?->bulkType?->units_per_bulk ?? 0) }}</span>
                                    </div>
                                @else
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Quantity (Units)') }}</label>
                                    <input type="number" min="1" wire:model.defer="entry_quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="button" wire:click="addProduct" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm text-white hover:bg-indigo-700">
                                {{ __('Add') }}
                            </button>
                        </div>

                        <div>
                            <div class="text-sm font-medium text-gray-700">{{ __('Payment') }}</div>

                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Method') }}</label>
                                    <select wire:model="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="cash">{{ __('Cash') }}</option>
                                        <option value="card">{{ __('Card') }}</option>
                                    </select>
                                    @error('payment_method') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Amount Paid') }}</label>
                                    <input type="number" min="0" step="0.01" wire:model.defer="amount_paid" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    @error('amount_paid') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="block text-sm font-medium text-gray-700">{{ __('Notes (optional)') }}</label>
                                <textarea wire:model.defer="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                @error('notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="mt-4 rounded-md bg-gray-50 p-4 text-sm text-gray-700 space-y-1">
                                <div class="flex items-center justify-between">
                                    <div>{{ __('Sub Total') }}</div>
                                    <div class="font-medium">{{ number_format((float) $subTotal, 2) }}</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>{{ __('Grand Total') }}</div>
                                    <div class="font-semibold text-gray-900">{{ number_format((float) $grandTotal, 2) }}</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>{{ __('Change Due') }}</div>
                                    <div class="font-medium">{{ number_format((float) $changeDue, 2) }}</div>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-end gap-3">
                                <button type="button" wire:click="clearCart" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">
                                    {{ __('Clear') }}
                                </button>
                                <button type="button" wire:click="finalizeSale" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm text-white hover:bg-indigo-700">
                                    {{ __('Finalize Sale') }}
                                </button>
                            </div>

                            @error('cart')
                                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Cart') }}</h3>
                            <div class="text-sm text-gray-500">
                                {{ __('Branch:') }}
                                <span class="font-medium">{{ $branches->firstWhere('id', $branch_id)?->name ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Available') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Qty') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Price') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                                        <th class="px-4 py-3"></th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($cartItems as $item)
                                        @php($available = $stockMap[$item['product_id']] ?? 0)
                                        <tr wire:key="cart-{{ $item['product_id'] }}">
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                <div class="font-medium">{{ $item['name'] }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-sm {{ $available < $item['quantity'] ? 'text-red-700' : 'text-gray-700' }}">
                                                {{ $available }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <div class="inline-flex items-center gap-2">
                                                    <button type="button" wire:click="decrementItem({{ $item['product_id'] }})" class="px-2 py-1 border border-gray-300 rounded">-</button>
                                                    <input type="number" min="1" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ (string) ($item['entry_mode'] ?? 'unit') === 'bulk' ? (int) ($item['bulk_quantity'] ?? 0) : (int) $item['quantity'] }}" wire:change="setQuantity({{ $item['product_id'] }}, $event.target.value)" />
                                                    <button type="button" wire:click="incrementItem({{ $item['product_id'] }})" class="px-2 py-1 border border-gray-300 rounded">+</button>
                                                </div>
                                                @if ((string) ($item['entry_mode'] ?? 'unit') === 'bulk')
                                                    <div class="mt-1 text-xs text-gray-500">{{ __('Units:') }} {{ (int) $item['quantity'] }}</div>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ number_format((float) $item['unit_price'], 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ number_format((float) $item['unit_price'] * (int) $item['quantity'], 2) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right">
                                                <button type="button" wire:click="removeItem({{ $item['product_id'] }})" class="text-red-600 hover:text-red-900">{{ __('Remove') }}</button>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if (count($cartItems) === 0)
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('Cart is empty.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Recent Sales') }}</h3>
                            @if ($selectedSale)
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium">{{ $selectedSale->receipt_no }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Receipt') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Branch') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                                            <th class="px-4 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($sales as $sale)
                                            <tr wire:key="sale-{{ $sale->id }}">
                                                <td class="px-4 py-3 text-sm text-gray-900">
                                                    <div class="font-medium">{{ $sale->receipt_no }}</div>
                                                    <div class="text-xs text-gray-500">{{ $sale->sold_at?->format('Y-m-d H:i') }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                    {{ $sale->branch?->name ?? '-' }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                    {{ number_format((float) $sale->grand_total, 2) }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-right">
                                                    <button type="button" wire:click="selectSale({{ $sale->id }})" class="text-indigo-600 hover:text-indigo-900">{{ __('View') }}</button>
                                                </td>
                                            </tr>
                                        @endforeach

                                        @if ($sales->isEmpty())
                                            <tr>
                                                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No sales yet.') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div>
                                <div class="rounded-md border border-gray-200">
                                    <div class="p-4 border-b border-gray-200">
                                        <div class="text-sm text-gray-500">{{ __('Sale Details') }}</div>
                                        @if ($selectedSale)
                                            <div class="mt-1 font-semibold text-gray-900">{{ $selectedSale->receipt_no }}</div>
                                            <div class="mt-1 text-sm text-gray-600">
                                                {{ $selectedSale->branch?->name ?? '-' }}
                                                @if ($selectedSale->user)
                                                    {{ '• ' . $selectedSale->user->name }}
                                                @endif
                                            </div>
                                            <div class="mt-2 text-sm text-gray-700">
                                                {{ __('Payment:') }}
                                                <span class="font-medium">{{ strtoupper($selectedSale->payment_method) }}</span>
                                            </div>

                                            <div class="mt-3">
                                                <a href="{{ route('sales.print', $selectedSale->id) }}" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-900 underline">
                                                    {{ __('Print Receipt') }}
                                                </a>
                                            </div>
                                            @if ($selectedSale->notes)
                                                <div class="mt-2 text-sm text-gray-700">{{ $selectedSale->notes }}</div>
                                            @endif
                                        @else
                                            <div class="mt-1 text-sm text-gray-600">{{ __('Select a sale to view details.') }}</div>
                                        @endif
                                    </div>

                                    <div class="p-4">
                                        @if ($selectedSale)
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Qty') }}</th>
                                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Price') }}</th>
                                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        @foreach ($selectedSale->items as $item)
                                                            <tr wire:key="sale-item-{{ $item->id }}">
                                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->product?->name ?? '-' }}</td>
                                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                                    @if ((string) $item->entry_mode === 'bulk')
                                                                        {{ (int) ($item->bulk_quantity ?? 0) }} {{ __('bulk') }}
                                                                        <span class="text-xs text-gray-500">({{ (int) $item->quantity }} {{ __('units') }})</span>
                                                                    @else
                                                                        {{ (int) $item->quantity }}
                                                                    @endif
                                                                </td>
                                                                <td class="px-4 py-3 text-sm text-gray-700">{{ number_format((float) $item->unit_price, 2) }}</td>
                                                                <td class="px-4 py-3 text-sm text-gray-700">{{ number_format((float) $item->line_total, 2) }}</td>
                                                            </tr>
                                                        @endforeach

                                                        @if ($selectedSale->items->isEmpty())
                                                            <tr>
                                                                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No items found.') }}</td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>

                                            <div class="mt-4 rounded-md bg-gray-50 p-4 text-sm text-gray-700 space-y-1">
                                                <div class="flex items-center justify-between">
                                                    <div>{{ __('Grand Total') }}</div>
                                                    <div class="font-semibold text-gray-900">{{ number_format((float) $selectedSale->grand_total, 2) }}</div>
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
                                                <div class="flex items-center justify-between">
                                                    <div>{{ __('Change') }}</div>
                                                    <div class="font-medium">{{ number_format((float) $selectedSale->change_due, 2) }}</div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
