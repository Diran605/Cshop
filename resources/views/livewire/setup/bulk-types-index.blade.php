<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Bulk Types') }}
            </h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">
                            {{ $editingId ? __('Edit Bulk Type') : __('New Bulk Type') }}
                        </h3>
                    </div>

                        @if ($bulkUnits->isEmpty())
                            <div class="mt-4 text-sm text-gray-600">
                                {{ __('Create at least one Bulk Unit before adding Bulk Types.') }}
                            </div>
                        @else
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Type Name') }}</label>
                                    <input type="text" wire:model.defer="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Bulk Unit') }}</label>
                                    <select wire:model.defer="bulk_unit_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="0">{{ __('Select...') }}</option>
                                        @foreach ($bulkUnits as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('bulk_unit_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">{{ __('Units Per Bulk') }}</label>
                                    <input type="number" min="1" wire:model.defer="units_per_bulk" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                    @error('units_per_bulk') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
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
                        @endif
                    </div>
                </div>

            <div class="lg:col-span-2 bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Name') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Bulk Unit') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Units') }}</th>
                                    <th class="px-4 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($bulkTypes as $type)
                                    <tr wire:key="bulk-type-{{ $type->id }}">
                                        <td class="px-4 py-3 text-sm text-gray-900">
                                            <div class="font-medium">{{ $type->name }}</div>
                                            @if ($type->description)
                                                <div class="text-xs text-gray-500">{{ $type->description }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $type->bulkUnit?->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700">
                                            {{ $type->units_per_bulk }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right">
                                            <div class="inline-flex items-center gap-3">
                                                <button type="button" wire:click.stop.prevent="edit({{ $type->id }})" class="text-indigo-600 hover:text-indigo-900">{{ __('Edit') }}</button>
                                                <button type="button" wire:click.stop.prevent="delete({{ $type->id }})" onclick="return confirm('Are you sure you want to delete this record?')" class="text-red-600 hover:text-red-900">{{ __('Delete') }}</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($bulkTypes->isEmpty())
                                    <tr>
                                        <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No bulk types found.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- If you do not have a consistent goal in life, you can not live it in a consistent way. - Marcus Aurelius --}}
</div>
