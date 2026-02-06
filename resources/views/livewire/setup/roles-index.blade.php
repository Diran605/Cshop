<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Roles') }}</h2>
            <div class="ui-page-subtitle">{{ __('Create roles and assign permissions per branch.') }}</div>
        </div>

        @if (session('status'))
            <div class="ui-alert-success">
                {{ session('status') }}
            </div>
        @endif

        <div class="space-y-6">
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Create Role') }}</h3>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="ui-label">{{ __('Branch') }}</label>
                            <select wire:model.live="branch_id" class="mt-1 ui-select">
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="ui-label">{{ __('Role Name') }}</label>
                            <input type="text" wire:model.defer="name" class="mt-1 ui-input" placeholder="e.g. cashier" />
                            @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="ui-label">{{ __('Permissions') }}</label>
                        <div class="mt-2 space-y-2">
                            @foreach ($this->groupedPermissions as $groupKey => $group)
                                @if (count($group['permissions']) > 0)
                                    <div class="border border-slate-200 rounded">
                                        <button type="button" wire:click="togglePermissionGroup('{{ $groupKey }}')" class="w-full flex items-center justify-between px-4 py-2 bg-slate-50 hover:bg-slate-100 rounded-t">
                                            <span class="font-medium text-slate-900">{{ $group['label'] }}</span>
                                            <svg class="w-4 h-4 transition-transform {{ in_array($groupKey, $expanded_permission_groups) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                            </svg>
                                        </button>
                                        @if (in_array($groupKey, $expanded_permission_groups))
                                            <div class="p-4 space-y-2">
                                                @foreach ($group['permissions'] as $perm)
                                                    <label class="flex items-center gap-2 text-sm text-slate-700">
                                                        <input type="checkbox" wire:model.defer="selected_permissions" value="{{ $perm->name }}" />
                                                        <span>{{ $perm->name }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @error('selected_permissions') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-3">
                        <button type="button" wire:click="save" class="ui-btn-primary">
                            {{ __('Save') }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Roles for Selected Branch') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Role') }}</th>
                                        <th>{{ __('Permissions') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $role)
                                        <tr wire:key="role-{{ $role->id }}">
                                            <td class="font-medium text-slate-900">
                                                <button type="button" wire:click="toggleRolePermissions({{ $role->id }})" class="flex items-center gap-2 hover:text-blue-600">
                                                    <svg class="w-4 h-4 transition-transform {{ $expanded_role_id === $role->id ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                    {{ $role->name }}
                                                </button>
                                            </td>
                                            <td class="text-slate-700">
                                                @if ($expanded_role_id === $role->id)
                                                    <div class="space-y-1">
                                                        @foreach ($role->permissions()->orderBy('name')->get() as $perm)
                                                            <span class="inline-block px-2 py-1 text-xs bg-slate-100 text-slate-700 rounded">{{ $perm->name }}</span>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    {{ $role->permissions()->pluck('name')->take(3)->implode(', ') }}{{ $role->permissions()->count() > 3 ? '...' : '' }}
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="inline-flex items-center gap-3">
                                                    <button type="button" wire:click="openEditModal({{ $role->id }})" class="ui-btn-link">{{ __('Edit') }}</button>
                                                    <button type="button" wire:click="openDeleteModal({{ $role->id }})" class="ui-btn-link-danger">{{ __('Delete') }}</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if ($roles->isEmpty())
                                        <tr>
                                            <td colspan="3" class="text-center text-sm text-slate-500">{{ __('No roles found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($show_delete_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeDeleteModal" data-modal-overlay></div>
            <div class="relative w-full max-w-lg mx-4 ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="text-sm text-slate-500">{{ __('Confirm Delete') }}</div>
                    <div class="mt-1 font-semibold text-slate-900">{{ __('Delete Role') }}</div>
                </div>

                <div class="p-4">
                    <div class="text-sm text-slate-700">
                        {{ __('Are you sure you want to delete this role?') }}
                        <span class="font-semibold">{{ $pending_delete_name ?: '-' }}</span>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-3">
                        <button type="button" wire:click="closeDeleteModal" class="ui-btn-secondary" data-modal-close>{{ __('Cancel') }}</button>
                        <button type="button" wire:click="confirmDelete" class="ui-btn-danger">{{ __('Delete') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($show_edit_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeEditModal" data-modal-overlay></div>
            <div class="relative w-full max-w-lg mx-4 ui-card">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Edit Role') }}</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $name ?: '-' }}</div>
                    </div>
                    <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                </div>

                <div class="p-4">
                    <div class="space-y-4">
                        <div>
                            <label class="ui-label">{{ __('Role Name') }}</label>
                            <input type="text" wire:model.defer="name" class="mt-1 ui-input" />
                            @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Permissions') }}</label>
                            <div class="mt-2 space-y-2 max-h-96 overflow-y-auto">
                                @foreach ($this->groupedPermissions as $groupKey => $group)
                                    @if (count($group['permissions']) > 0)
                                        <div class="border border-slate-200 rounded">
                                            <button type="button" wire:click="togglePermissionGroup('{{ $groupKey }}')" class="w-full flex items-center justify-between px-4 py-2 bg-slate-50 hover:bg-slate-100 rounded-t">
                                                <span class="font-medium text-slate-900">{{ $group['label'] }}</span>
                                                <svg class="w-4 h-4 transition-transform {{ in_array($groupKey, $expanded_permission_groups) ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                            @if (in_array($groupKey, $expanded_permission_groups))
                                                <div class="p-4 space-y-2">
                                                    @foreach ($group['permissions'] as $perm)
                                                        <label class="flex items-center gap-2 text-sm text-slate-700">
                                                            <input type="checkbox" wire:model.defer="selected_permissions" value="{{ $perm->name }}" />
                                                            <span>{{ $perm->name }}</span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-3">
                        <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>{{ __('Cancel') }}</button>
                        <button type="button" wire:click="save" class="ui-btn-primary">{{ __('Update') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
