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

                        <div class="flex items-center justify-end">
                            <button type="button" wire:click="save" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm text-white hover:bg-indigo-700">
                                {{ __('Post Stock In') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white shadow-sm sm:rounded-lg">
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
        </div>
    </div>

    {{-- Order your soul. Reduce your wants. - Augustine --}}
</div>
