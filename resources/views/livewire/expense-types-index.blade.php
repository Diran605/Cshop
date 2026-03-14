<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-bold tracking-wider uppercase text-emerald-600 mb-1">Expenses</div>
                    <h1 class="text-2xl font-bold text-slate-900">Expense Types</h1>
                </div>
                <button type="button" wire:click="openCreateModal" class="ui-btn-primary">
                    {{ __('Add Type') }}
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="ui-alert-success">{{ session('status') }}</div>
        @endif

        {{-- Filters --}}
        <div class="ui-card mb-6">
            <div class="ui-card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if ($isSuperAdmin)
                        <div>
                            <label class="ui-label">{{ __('Branch') }}</label>
                            <select wire:model.live="branch_id" class="mt-1 ui-select">
                                <option value="0">{{ __('All Branches') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div class="{{ $isSuperAdmin ? '' : 'md:col-span-2' }}">
                        <label class="ui-label">{{ __('Search') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="mt-1 ui-input" placeholder="{{ __('Search expense types...') }}" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="ui-card">
            <div class="ui-card-body p-0">
                <div class="overflow-x-auto">
                    <table class="ui-table min-w-full">
                        <thead>
                            <tr>
                                @if ($isSuperAdmin)
                                    <th>{{ __('Branch') }}</th>
                                @endif
                                <th>{{ __('Name') }}</th>
                                <th>{{ __('Description') }}</th>
                                <th class="text-center">{{ __('Status') }}</th>
                                <th class="text-center">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($expenseTypes as $type)
                                <tr wire:key="type-{{ $type->id }}">
                                    @if ($isSuperAdmin)
                                        <td class="whitespace-nowrap">{{ $type->branch?->name ?? '-' }}</td>
                                    @endif
                                    <td class="whitespace-nowrap font-medium">{{ $type->name }}</td>
                                    <td class="text-slate-600">{{ $type->description ?? '-' }}</td>
                                    <td class="text-center">
                                        @if ($type->is_active)
                                            <span class="ui-badge-success">{{ __('Active') }}</span>
                                        @else
                                            <span class="ui-badge-warning">{{ __('Inactive') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button type="button" wire:click="openEditModal({{ $type->id }})" class="ui-btn-link text-xs">
                                            {{ __('Edit') }}
                                        </button>
                                        <button type="button" wire:click="openDeleteModal({{ $type->id }})" class="ui-btn-link text-xs text-red-600">
                                            {{ __('Delete') }}
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isSuperAdmin ? 5 : 4 }}" class="ui-table-empty">
                                        {{ __('No expense types found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($expenseTypes->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $expenseTypes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if ($show_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal" data-modal-overlay></div>
            <div class="relative w-full max-w-md ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="font-semibold text-slate-900">
                        {{ $editing_id > 0 ? __('Edit Expense Type') : __('Add Expense Type') }}
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    <div>
                        <label class="ui-label">{{ __('Name') }}</label>
                        <input type="text" wire:model.defer="expense_type_name" class="mt-1 ui-input" />
                        @error('expense_type_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Description') }}</label>
                        <textarea wire:model.defer="expense_type_description" class="mt-1 ui-input" rows="2"></textarea>
                        @error('expense_type_description') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" id="is_active" wire:model="expense_type_is_active" class="rounded border-slate-300" />
                        <label for="is_active" class="text-sm text-slate-700">{{ __('Active') }}</label>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-end gap-3">
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

    {{-- Delete Confirmation Modal --}}
    @if ($show_delete_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeDeleteModal" data-modal-overlay></div>
            <div class="relative w-full max-w-sm ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="font-semibold text-slate-900">{{ __('Confirm Void') }}</div>
                </div>
                <div class="p-4">
                    <p class="text-sm text-slate-700">
                        {{ __('Are you sure you want to void expense type:') }}
                        <strong>{{ $pending_delete_name }}</strong>?
                    </p>
                </div>
                <div class="p-4 border-t border-slate-200 flex justify-end gap-3">
                    <button type="button" wire:click="closeDeleteModal" class="ui-btn-secondary" data-modal-close>
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" wire:click="confirmDelete" class="ui-btn-danger">
                        {{ __('Void') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
