<div class="ui-page">
    <div class="ui-page-container">
        @if (session()->has('status'))
            <div class="mb-4 ui-alert ui-alert-success">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Unit Types') }}</h2>
            <div class="ui-page-subtitle">{{ __('Manage unit types for products (bottles, packets, strips, etc.)') }}</div>
        </div>

        <div class="ui-card">
            <div class="ui-card-body">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                    <div class="w-full sm:flex-1 sm:max-w-md">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Search unit types...') }}" class="ui-input" />
                    </div>
                    <button type="button" wire:click="openModal" class="ui-btn-primary w-full sm:w-auto">
                        {{ __('Add Unit Type') }}
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="ui-table">
                        <thead>
                            <tr>
                                @if ($isSuperAdmin)
                                    <th>{{ __('Branch') }}</th>
                                @endif
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Status') }}</th>
                                <th>{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($unitTypes as $unitType)
                                <tr wire:key="ut-{{ $unitType->id }}">
                                    @if ($isSuperAdmin)
                                        <td>{{ $unitType->branch?->name ?? '-' }}</td>
                                    @endif
                                    <td>{{ $unitType->name }}</td>
                                    <td>
                                        <button type="button" wire:click="toggleActive({{ $unitType->id }})" class="ui-badge {{ $unitType->is_active ? 'ui-badge-success' : 'ui-badge-secondary' }}">
                                            {{ $unitType->is_active ? __('Active') : __('Inactive') }}
                                        </button>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <button type="button" wire:click="edit({{ $unitType->id }})" class="ui-btn-link">{{ __('Edit') }}</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach

                            @if ($unitTypes->isEmpty())
                                <tr>
                                    <td colspan="{{ $isSuperAdmin ? 4 : 3 }}" class="ui-table-empty">{{ __('No unit types found.') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                @if ($unitTypes->hasPages())
                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-slate-600">
                            {{ __('Showing') }} {{ $unitTypes->firstItem() }} {{ __('to') }} {{ $unitTypes->lastItem() }} {{ __('of') }} {{ $unitTypes->total() }} {{ __('results') }}
                        </div>
                        {{ $unitTypes->links('pagination::tailwind') }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @if ($show_modal)
        <div class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-8 sm:pt-12 overflow-y-auto" data-modal-root>
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal" data-modal-overlay></div>
            <div class="relative w-full max-w-lg ui-card flex flex-col mb-4 z-10">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between shrink-0">
                    <div>
                        <div class="text-sm text-slate-500">{{ $editingId ? __('Edit Unit Type') : __('Add Unit Type') }}</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $name ?: '-' }}</div>
                    </div>
                    <button type="button" wire:click="closeModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                </div>

                <div class="p-4 overflow-y-auto flex-1 min-h-0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if ($isSuperAdmin)
                            <div class="sm:col-span-2">
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

                        <div class="sm:col-span-2">
                            <label class="ui-label">{{ __('Name') }}</label>
                            <input type="text" wire:model.defer="name" placeholder="{{ __('e.g., Bottle, Packet, Strip, Can') }}" class="mt-1 ui-input" />
                            @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model="is_active" class="rounded border-slate-300" />
                                <span class="text-sm text-slate-700">{{ __('Active') }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 shrink-0">
                    <button type="button" wire:click="closeModal" class="ui-btn-secondary" data-modal-close>
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" wire:click="save" class="ui-btn-primary">
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
