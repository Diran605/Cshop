<div>
<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('User Roles') }}</h2>
            <div class="ui-page-subtitle">{{ __('Assign branch-scoped roles to users.') }}</div>
        </div>

        @if (session('status'))
            <div class="ui-alert-success">
                {{ session('status') }}
            </div>
        @endif

        <div class="ui-card">
            <div class="ui-card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="ui-label">{{ __('Branch') }}</label>
                        <select wire:model.live="branch_id" class="mt-1 ui-select">
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="md:col-span-2">
                        <label class="ui-label">{{ __('User') }}</label>
                        <select wire:model.live="user_id" class="mt-1 ui-select">
                            <option value="0">{{ __('Select...') }}</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                            @endforeach
                        </select>
                        @error('user_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mt-4">
                    <label class="ui-label">{{ __('Roles (Branch Scoped)') }}</label>
                    <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                        @foreach ($roles as $role)
                            <label class="flex items-center gap-2 text-sm text-slate-700">
                                <input type="checkbox" wire:model.defer="selected_roles" value="{{ $role->name }}" />
                                <span>{{ $role->name }}</span>
                            </label>
                        @endforeach

                        @if ($roles->isEmpty())
                            <div class="text-sm text-slate-500">{{ __('No roles found for this branch.') }}</div>
                        @endif
                    </div>
                </div>

                <div class="mt-4 flex items-center justify-end gap-3">
                    <button type="button" wire:click="save" class="ui-btn-primary">
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="ui-card mt-6">
            <div class="ui-card-body">
                <h3 class="ui-card-title">{{ __('Current Role Assignments') }}</h3>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="ui-label">{{ __('Filter by Branch') }}</label>
                        <select wire:model.live="filter_branch_id" class="mt-1 ui-select">
                            <option value="0">{{ __('All Branches') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Filter by User') }}</label>
                        <select wire:model.live="filter_user_id" class="mt-1 ui-select">
                            <option value="0">{{ __('All Users') }}</option>
                            @foreach ($filtered_users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Filter by Role') }}</label>
                        <select wire:model.live="filter_role" class="mt-1 ui-select">
                            <option value="">{{ __('All Roles') }}</option>
                            @foreach ($all_roles as $role)
                                <option value="{{ $role }}">{{ $role }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('User') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Roles') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($user_role_assignments as $assignment)
                                    <tr wire:key="ura-{{ $assignment['user_id'] }}">
                                        <td>{{ $assignment['user_name'] }}</td>
                                        <td>{{ $assignment['user_email'] }}</td>
                                        <td>{{ $assignment['branch_name'] }}</td>
                                        <td>
                                            @if (empty($assignment['roles']))
                                                <span class="text-slate-400">-</span>
                                            @else
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach ($assignment['roles'] as $role)
                                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-soft-blue text-primary-blue">
                                                            {{ $role }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <div class="inline-flex items-center gap-3">
                                                <button type="button" wire:click.stop.prevent="openViewModal({{ $assignment['user_id'] }})" class="ui-btn-link">{{ __('View') }}</button>
                                                <button type="button" wire:click.stop.prevent="openEditModal({{ $assignment['user_id'] }})" class="ui-btn-link">{{ __('Edit') }}</button>
                                                <button type="button" wire:click.stop.prevent="openDeleteModal({{ $assignment['user_id'] }})" class="ui-btn-link-danger">{{ __('Delete') }}</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                @if (empty($user_role_assignments))
                                    <tr>
                                        <td colspan="5" class="ui-table-empty">{{ __('No role assignments found.') }}</td>
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

<!-- View Modal -->
@if ($show_view_modal)
    <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeViewModal" data-modal-overlay></div>
        <div class="relative w-full max-w-lg mx-4 ui-card">
            <div class="p-4 border-b border-slate-200">
                <div class="text-sm text-slate-500">{{ __('View Roles') }}</div>
                <div class="mt-1 font-semibold text-slate-900">{{ __('User Role Details') }}</div>
            </div>
            <div class="p-4">
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-slate-500">{{ __('User') }}</label>
                        <div class="mt-1 text-sm text-slate-900">{{ $this->viewUser?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500">{{ __('Email') }}</label>
                        <div class="mt-1 text-sm text-slate-900">{{ $this->viewUser?->email ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500">{{ __('Branch') }}</label>
                        <div class="mt-1 text-sm text-slate-900">{{ $this->viewUser?->branch?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500">{{ __('Roles') }}</label>
                        <div class="mt-1">
                            @if ($this->viewUser && $this->viewUser->roles && count($this->viewUser->roles) > 0)
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($this->viewUser->roles as $role)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-soft-blue text-primary-blue">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-slate-400 text-sm">-</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex items-center justify-end">
                    <button type="button" wire:click="closeViewModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Edit Modal -->
@if ($show_edit_modal)
    <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeEditModal" data-modal-overlay></div>
        <div class="relative w-full max-w-lg mx-4 ui-card">
            <div class="p-4 border-b border-slate-200">
                <div class="text-sm text-slate-500">{{ __('Edit Roles') }}</div>
                <div class="mt-1 font-semibold text-slate-900">{{ __('Update User Roles') }}</div>
            </div>
            <div class="p-4">
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-slate-500">{{ __('User') }}</label>
                        <div class="mt-1 text-sm text-slate-900">{{ $this->editUser?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500">{{ __('Branch') }}</label>
                        <div class="mt-1 text-sm text-slate-900">{{ $this->editUser?->branch?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-slate-500">{{ __('Roles') }}</label>
                        <div class="mt-2 space-y-2">
                            @if ($this->editRoles && count($this->editRoles) > 0)
                                @foreach ($this->editRoles as $role)
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input type="checkbox" wire:model.live="edit_selected_roles" value="{{ $role->name }}" class="rounded border-gray-300 text-primary-blue focus:ring-primary-blue" />
                                        <span class="text-sm text-slate-700">{{ $role->name }}</span>
                                    </label>
                                @endforeach
                            @else
                                <div class="text-sm text-slate-500">{{ __('No roles available for this branch.') }}</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="mt-6 flex items-center justify-end gap-3">
                    <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>{{ __('Cancel') }}</button>
                    <button type="button" wire:click="saveEdit" class="ui-btn-primary">{{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Delete Modal -->
@if ($show_delete_modal)
    <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeDeleteModal" data-modal-overlay></div>
        <div class="relative w-full max-w-lg mx-4 ui-card">
            <div class="p-4 border-b border-slate-200">
                <div class="text-sm text-slate-500">{{ __('Confirm Delete') }}</div>
                <div class="mt-1 font-semibold text-slate-900">{{ __('Remove All Roles') }}</div>
            </div>
            <div class="p-4">
                <div class="text-sm text-slate-700">
                    {{ __('Are you sure you want to remove all roles from this user?') }}
                    <span class="font-semibold">{{ $this->deleteUser?->name ?? '-' }}</span>
                </div>
                <div class="mt-4 flex items-center justify-end gap-3">
                    <button type="button" wire:click="closeDeleteModal" class="ui-btn-secondary" data-modal-close>{{ __('Cancel') }}</button>
                    <button type="button" wire:click="confirmDelete" class="ui-btn-danger">{{ __('Remove Roles') }}</button>
                </div>
            </div>
        </div>
    </div>
@endif
</div>
</div>
