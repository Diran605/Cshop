<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Bulk Types') }}</h2>
            <div class="ui-page-subtitle">{{ __('Define reusable bulk packaging configurations.') }}</div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @if (! $show_edit_modal)
                <div class="ui-card">
                    <div class="ui-card-body">
                        <div class="flex items-center justify-between">
                            <h3 class="ui-card-title">
                                {{ __('New Bulk Type') }}
                            </h3>
                        </div>

                        @if ($bulkUnits->isEmpty())
                            <div class="mt-4 text-sm text-slate-600">
                                {{ __('Create at least one Bulk Unit before adding Bulk Types.') }}
                            </div>
                        @else
                            <div class="mt-4 space-y-4">
                                <div>
                                    <label class="ui-label">{{ __('Branch') }}</label>
                                    @if ($isSuperAdmin)
                                        <select wire:model.live="branch_id" @disabled($editingId) class="mt-1 ui-select">
                                            <option value="0">{{ __('Select...') }}</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('branch_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                    @else
                                        <div class="mt-1 rounded-lg border border-slate-300/80 bg-white/60 px-3 py-2 text-sm text-slate-700">
                                            {{ auth()->user()?->branch?->name ?? '-' }}
                                        </div>
                                        <input type="hidden" wire:model="branch_id" />
                                    @endif
                                </div>

                                <div>
                                    <label class="ui-label">{{ __('Type Name') }}</label>
                                    <input type="text" wire:model.defer="name" class="mt-1 ui-input" />
                                    @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">{{ __('Bulk Unit') }}</label>
                                    <select wire:model.defer="bulk_unit_id" class="mt-1 ui-select">
                                        <option value="0">{{ __('Select...') }}</option>
                                        @foreach ($bulkUnits as $unit)
                                            <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('bulk_unit_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">{{ __('Units Per Bulk') }}</label>
                                    <input type="number" min="1" wire:model.defer="units_per_bulk" class="mt-1 ui-input" />
                                    @error('units_per_bulk') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div>
                                    <label class="ui-label">{{ __('Description') }}</label>
                                    <textarea wire:model.defer="description" rows="3" class="mt-1 ui-input"></textarea>
                                    @error('description') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                </div>

                                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                                    <button type="button" wire:click="save" class="ui-btn-primary w-full sm:w-auto">
                                        {{ __('Create') }}
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="lg:col-span-2 ui-card">
                <div class="ui-card-body">
                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                        <div class="w-full sm:max-w-md">
                            <label class="ui-label">{{ __('Search') }}</label>
                            <input type="text" wire:model.live.debounce.300ms="search" class="mt-1 ui-input" placeholder="Search bulk types..." />
                        </div>
                    </div>

                    <div class="overflow-x-auto mt-4">
                        <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    @if ($isSuperAdmin)
                                        <th>{{ __('Branch') }}</th>
                                    @endif
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Bulk Unit') }}</th>
                                    <th>{{ __('Units') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bulkTypes as $type)
                                    <tr wire:key="bulk-type-{{ $type->id }}">
                                        @if ($isSuperAdmin)
                                            <td>
                                                {{ $type->branch?->name ?? '-' }}
                                            </td>
                                        @endif
                                        <td class="text-slate-900">
                                            <div class="font-medium">{{ $type->name }}</div>
                                            @if ($type->description)
                                                <div class="text-xs text-slate-500">{{ $type->description }}</div>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $type->bulkUnit?->name ?? '-' }}
                                        </td>
                                        <td>
                                            {{ (int) $type->units_per_bulk }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-right">
                                            <div class="inline-flex items-center gap-3">
                                                <button type="button" wire:click.stop.prevent="openEditModal({{ $type->id }})" class="ui-btn-link">{{ __('Edit') }}</button>
                                                <button type="button" wire:click.stop.prevent="openDeleteModal({{ $type->id }})" class="ui-btn-link-danger">{{ __('Delete') }}</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($bulkTypes->isEmpty())
                                    <tr>
                                        <td colspan="{{ $isSuperAdmin ? 5 : 4 }}" class="ui-table-empty">{{ __('No bulk types found.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                        </div>
                    </div>

                    @if ($bulkTypes->hasPages())
                        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="text-sm text-slate-600">
                                {{ __('Showing') }} {{ $bulkTypes->firstItem() }} {{ __('to') }} {{ $bulkTypes->lastItem() }} {{ __('of') }} {{ $bulkTypes->total() }} {{ __('results') }}
                            </div>
                            {{ $bulkTypes->links('pagination::tailwind') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($show_delete_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeDeleteModal" data-modal-overlay></div>
            <div class="relative w-full max-w-lg ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="text-sm text-slate-500">{{ __('Confirm Delete') }}</div>
                    <div class="mt-1 font-semibold text-slate-900">{{ __('Delete Bulk Type') }}</div>
                </div>

                <div class="p-4">
                    <div class="text-sm text-slate-700">
                        {{ __('Are you sure you want to delete this bulk type?') }}
                        <span class="font-semibold">{{ $pending_delete_name ?: '-' }}</span>
                    </div>

                    <div class="mt-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                        <button type="button" wire:click="closeDeleteModal" class="ui-btn-secondary w-full sm:w-auto" data-modal-close>{{ __('Cancel') }}</button>
                        <button type="button" wire:click="confirmDelete" class="ui-btn-danger w-full sm:w-auto">{{ __('Delete') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($show_edit_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeEditModal" data-modal-overlay></div>
            <div class="relative w-full max-w-lg ui-card">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Edit Bulk Type') }}</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $name ?: '-' }}</div>
                    </div>
                    <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                </div>

                <div class="p-4">
                    <div class="space-y-4">
                        <div>
                            <label class="ui-label">{{ __('Branch') }}</label>
                            @if ($isSuperAdmin)
                                <select wire:model.live="branch_id" @disabled(true) class="mt-1 ui-select">
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <div class="mt-1 rounded-lg border border-slate-300/80 bg-white/60 px-3 py-2 text-sm text-slate-700">
                                    {{ auth()->user()?->branch?->name ?? '-' }}
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Type Name') }}</label>
                            <input type="text" wire:model.defer="name" class="mt-1 ui-input" />
                            @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Bulk Unit') }}</label>
                            <select wire:model.defer="bulk_unit_id" class="mt-1 ui-select">
                                <option value="0">{{ __('Select...') }}</option>
                                @foreach ($bulkUnits as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('bulk_unit_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Units Per Bulk') }}</label>
                            <input type="number" min="1" wire:model.defer="units_per_bulk" class="mt-1 ui-input" />
                            @error('units_per_bulk') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Description') }}</label>
                            <textarea wire:model.defer="description" rows="3" class="mt-1 ui-input"></textarea>
                            @error('description') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mt-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                        <button type="button" wire:click="closeEditModal" class="ui-btn-secondary w-full sm:w-auto" data-modal-close>{{ __('Cancel') }}</button>
                        <button type="button" wire:click="save" class="ui-btn-primary w-full sm:w-auto">{{ __('Save') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- If you do not have a consistent goal in life, you can not live it in a consistent way. - Marcus Aurelius --}}
</div>
