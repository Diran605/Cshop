<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Stock In') }}
            </h2>
        </div>

        @if (session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-800">
                {{ session('status') }}
            </div>
        @endif

        <div class="space-y-6">
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Receive Stock') }}</h3>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Branch') }}</label>
                            @if ($isSuperAdmin)
                                <select wire:model.live="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
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
                            <input type="text" wire:model.live.debounce.300ms="product_search" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Search product..." />
                            <select wire:model="product_id" @disabled($isSuperAdmin && $branch_id <= 0) class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100">
                                <option value="0">{{ __('Select...') }}</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error('product_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror

                            @if ($isSuperAdmin && $branch_id <= 0)
                                <div class="mt-1 text-xs text-gray-500">{{ __('Select a branch first to load products.') }}</div>
                            @endif
                        </div>

                        @if ($selectedProduct && (bool) $selectedProduct->bulk_enabled)
                            <div>
                                <label class="block text-sm font-medium text-gray-700">{{ __('Entry Type') }}</label>
                                <select wire:model="entry_mode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="unit">{{ __('Units') }}</option>
                                    <option value="bulk">{{ __('Bulk') }}</option>
                                </select>
                                @error('entry_mode') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>
                        @endif

                        <div>
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
                                @error('bulk_quantity') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            @else
                                <label class="block text-sm font-medium text-gray-700">{{ __('Quantity (Units)') }}</label>
                                <input type="number" min="1" wire:model.defer="quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                @error('quantity') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            @endif
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Cost Price (optional)') }}</label>
                            <input type="number" min="0" step="0.01" wire:model.defer="cost_price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            @error('cost_price') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Notes (optional)') }}</label>
                            <textarea wire:model.defer="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            @error('notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="button" wire:click="save" class="ui-btn-primary">
                                {{ __('Post Stock In') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-6">
                <div class="ui-card">
                    <div class="ui-card-body">
                        <div class="flex items-center justify-between">
                            <h3 class="ui-card-title">{{ __('Current Stock') }}</h3>
                            <div class="text-sm text-gray-500">
                                {{ __('Branch:') }}
                                <span class="font-medium">{{ $branches->firstWhere('id', $branch_id)?->name ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="mt-3 max-w-md">
                            <label class="block text-sm font-medium text-gray-700">{{ __('Search') }}</label>
                            <input type="text" wire:model.live.debounce.300ms="stock_search" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Search stock..." />
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Current') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Min') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Cost') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($stocks as $stock)
                                        <tr wire:key="stock-{{ $stock->id }}">
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                {{ $stock->product?->name ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ $stock->current_stock }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ $stock->minimum_stock }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                {{ $stock->cost_price !== null ? number_format((float) $stock->cost_price, 2) : '-' }}
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if ($stocks->isEmpty())
                                        <tr>
                                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No stock rows found for this branch.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="ui-card">
                    <div class="ui-card-body">
                        <div class="flex items-center justify-between">
                            <h3 class="ui-card-title">{{ __('Recent Stock In Receipts') }}</h3>
                            <div class="w-72">
                                <input type="text" wire:model.live.debounce.300ms="receipt_search" placeholder="{{ __('Search receipts...') }}" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            </div>
                            @if ($selectedReceipt)
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium">{{ $selectedReceipt->receipt_no }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <div class="overflow-x-auto">
                                <table class="ui-table">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Receipt') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Branch') }}</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Qty') }}</th>
                                            <th class="px-4 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach ($receipts as $receipt)
                                            <tr wire:key="receipt-{{ $receipt->id }}">
                                                <td class="px-4 py-3 text-sm text-gray-900">
                                                    <div class="font-medium">{{ $receipt->receipt_no }}</div>
                                                    <div class="text-xs text-gray-500">{{ $receipt->received_at?->format('Y-m-d H:i') }}</div>
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                    {{ $receipt->branch?->name ?? '-' }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-gray-700">
                                                    {{ $receipt->total_quantity }}
                                                </td>
                                                <td class="px-4 py-3 text-sm text-right">
                                                    <button type="button" wire:click="openReceiptModal({{ $receipt->id }})" class="ui-btn-link">{{ __('View') }}</button>
                                                </td>
                                            </tr>
                                        @endforeach

                                        @if ($receipts->isEmpty())
                                            <tr>
                                                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No receipts yet.') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            <div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($show_receipt_modal && $selectedReceipt)
            <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeReceiptModal" data-modal-overlay></div>
                <div class="relative w-full max-w-3xl mx-4 ui-card">
                    <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-gray-500">{{ __('Receipt Details') }}</div>
                            <div class="mt-1 font-semibold text-gray-900">{{ $selectedReceipt->receipt_no }}</div>
                            <div class="mt-1 text-sm text-gray-600">
                                {{ $selectedReceipt->branch?->name ?? '-' }}
                                @if ($selectedReceipt->user)
                                    {{ '• ' . $selectedReceipt->user->name }}
                                @endif
                                {{ '• ' . $selectedReceipt->received_at?->format('Y-m-d H:i') }}
                            </div>
                        </div>

                        <button type="button" wire:click="closeReceiptModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                    </div>

                    <div class="p-4">
                        @if ($selectedReceipt->notes)
                            <div class="text-sm text-gray-700">{{ $selectedReceipt->notes }}</div>
                        @endif

                        <div class="mt-4 overflow-x-auto">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Qty') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Cost') }}</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($selectedReceipt->items as $item)
                                        <tr wire:key="receipt-modal-item-{{ $item->id }}">
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $item->product?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                @if ((string) $item->entry_mode === 'bulk')
                                                    {{ (int) ($item->bulk_quantity ?? 0) }} {{ __('bulk') }}
                                                    <span class="text-xs text-gray-500">({{ (int) $item->quantity }} {{ __('units') }})</span>
                                                @else
                                                    {{ (int) $item->quantity }}
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ $item->cost_price !== null ? number_format((float) $item->cost_price, 2) : '-' }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-700">{{ $item->line_total !== null ? number_format((float) $item->line_total, 2) : '-' }}</td>
                                        </tr>
                                    @endforeach

                                    @if ($selectedReceipt->items->isEmpty())
                                        <tr>
                                            <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No items found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Order your soul. Reduce your wants. - Augustine --}}
</div>
