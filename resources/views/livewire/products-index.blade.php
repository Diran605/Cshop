<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Products') }}
            </h2>
        </div>

        <div class="space-y-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $editingId ? __('Edit Product') : __('New Product') }}
                        </h3>
                    </div>

                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
                            <input type="text" wire:model.defer="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Category') }}</label>
                            <select wire:model.defer="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Selling Price') }}</label>
                            <input type="number" min="0" step="0.01" wire:model.defer="selling_price" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                            @error('selling_price') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                            <select wire:model.defer="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                            @error('status') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model="bulk_enabled" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                <span class="ms-2 text-sm text-gray-700">{{ __('Enable Bulk') }}</span>
                            </label>
                            @error('bulk_enabled') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Bulk Type') }}</label>
                            <select wire:model.defer="bulk_type_id" @disabled(! $bulk_enabled) class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100">
                                <option value="">{{ __('Select...') }}</option>
                                @foreach ($bulkTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('bulk_type_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
                            <textarea wire:model.defer="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                            @error('description') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            @if ($editingId)
                                <button type="button" wire:click="cancelEdit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">
                                    {{ __('Cancel') }}
                                </button>
                            @endif

                            <button type="button" wire:click="save" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm text-white hover:bg-indigo-700">
                                {{ $editingId ? __('Save') : __('Create') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Category') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Price') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Bulk') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($products as $product)
                                    <tr wire:key="product-{{ $product->id }}">
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <div class="font-medium">{{ $product->name }}</div>
                                            @if ($product->description)
                                                <div class="text-xs text-gray-500">{{ $product->description }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $product->category?->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ number_format((float) $product->selling_price, 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            @if ($product->bulk_enabled)
                                                {{ $product->bulkType?->name ?? '-' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right">
                                            <div class="inline-flex items-center gap-3">
                                                <button type="button" wire:click.stop.prevent="edit({{ $product->id }})" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</button>
                                                <button type="button" wire:click.stop.prevent="delete({{ $product->id }})" onclick="return confirm('Are you sure you want to delete this record?')" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($products->isEmpty())
                                    <tr>
                                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No products found.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Smile, breathe, and go slowly. - Thich Nhat Hanh --}}
</div>
