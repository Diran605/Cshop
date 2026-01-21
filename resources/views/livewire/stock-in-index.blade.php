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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Receive Stock') }}</h3>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Branch') }}</label>
                            <select wire:model="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="0">{{ __('Select...') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Product') }}</label>
                            <select wire:model="product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="0">{{ __('Select...') }}</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                            @error('product_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Quantity') }}</label>
                            <input type="number" min="1" wire:model.defer="quantity" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            @error('quantity') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
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
                            <button type="button" wire:click="save" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm text-white hover:bg-indigo-700">
                                {{ __('Post Stock In') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Current Stock') }}</h3>
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

                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">{{ __('Recent Stock In Receipts') }}</h3>
                            @if ($selectedReceipt)
                                <div class="text-sm text-gray-500">
                                    <span class="font-medium">{{ $selectedReceipt->receipt_no }}</span>
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
                                                    <button type="button" wire:click="selectReceipt({{ $receipt->id }})" class="text-indigo-600 hover:text-indigo-900">{{ __('View') }}</button>
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

                            <div>
                                <div class="rounded-md border border-gray-200">
                                    <div class="p-4 border-b border-gray-200">
                                        <div class="text-sm text-gray-500">{{ __('Receipt Details') }}</div>
                                        @if ($selectedReceipt)
                                            <div class="mt-1 font-semibold text-gray-900">{{ $selectedReceipt->receipt_no }}</div>
                                            <div class="mt-1 text-sm text-gray-600">
                                                {{ $selectedReceipt->branch?->name ?? '-' }}
                                                @if ($selectedReceipt->user)
                                                    {{ '• ' . $selectedReceipt->user->name }}
                                                @endif
                                            </div>
                                            @if ($selectedReceipt->notes)
                                                <div class="mt-2 text-sm text-gray-700">{{ $selectedReceipt->notes }}</div>
                                            @endif
                                        @else
                                            <div class="mt-1 text-sm text-gray-600">{{ __('Select a receipt to view details.') }}</div>
                                        @endif
                                    </div>

                                    <div class="p-4">
                                        @if ($selectedReceipt)
                                            <div class="overflow-x-auto">
                                                <table class="min-w-full divide-y divide-gray-200">
                                                    <thead class="bg-gray-50">
                                                        <tr>
                                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Qty') }}</th>
                                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Cost') }}</th>
                                                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="bg-white divide-y divide-gray-200">
                                                        @foreach ($selectedReceipt->items as $item)
                                                            <tr wire:key="receipt-item-{{ $item->id }}">
                                                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->product?->name ?? '-' }}</td>
                                                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->quantity }}</td>
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

    {{-- Order your soul. Reduce your wants. - Augustine --}}
</div>
