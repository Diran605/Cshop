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
            {{-- Create/Edit Role Form --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ $editing_role_id ? __('Edit Role') : __('Create Role') }}</h3>

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

                    {{-- Master Toggle --}}
                    <div class="mt-6 p-4 bg-gradient-to-r from-slate-50 to-slate-100 rounded-lg border border-slate-200">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-800">{{ __('All Permissions') }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    {{ $permission_stats['selected'] }} of {{ $permission_stats['total'] }} selected
                                    ({{ $permission_stats['percentage'] }}%)
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-32 h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-primary-blue rounded-full transition-all duration-300" style="width: {{ $permission_stats['percentage'] }}%"></div>
                                </div>
                                <button type="button" 
                                    wire:click="{{ $permission_stats['percentage'] === 100 ? 'revokeAllPermissions' : 'grantAllPermissions' }}"
                                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors {{ $permission_stats['percentage'] === 100 ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-primary-blue text-white hover:bg-blue-600' }}">
                                    {{ $permission_stats['percentage'] === 100 ? __('Clear All') : __('Select All') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Permissions Grid --}}
                    <div class="mt-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                            <label class="ui-label mb-0">{{ __('Permissions') }}</label>
                            <div class="flex items-center gap-2">
                                <div class="relative">
                                    <input type="text" 
                                        wire:model.live.debounce.300ms="permission_search" 
                                        placeholder="{{ __('Search permissions...') }}" 
                                        class="ui-input pl-8 w-full sm:w-64" />
                                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                @if ($permission_stats['selected'] > 0)
                                    <button type="button" 
                                        wire:click="revokeAllPermissions"
                                        class="px-3 py-1.5 text-xs font-medium rounded-md bg-red-100 text-red-700 hover:bg-red-200 transition-colors whitespace-nowrap">
                                        {{ __('Clear All') }}
                                    </button>
                                @endif
                            </div>
                        </div>
                        
                        @if ($permission_search !== '')
                            <div class="mb-3 text-xs text-slate-500">
                                {{ __('Showing') }} {{ collect($this->filtered_permissions)->sum('total') }} {{ __('permissions matching') }} "{{ $permission_search }}"
                            </div>
                        @endif
                        
                        <div class="space-y-2">
                            @php
                                $groupIcons = [
                                    'branches' => '🏢',
                                    'users' => '👥',
                                    'rbac' => '🔐',
                                    'products' => '📦',
                                    'stock_in' => '📥',
                                    'sales' => '💰',
                                    'expenses' => '💸',
                                    'reports' => '📊',
                                    'audit' => '🔍',
                                ];
                            @endphp
                            
                            @foreach ($this->filtered_permissions as $groupKey => $group)
                                @if (count($group['permissions']) > 0)
                                    @php 
                                        $iconKey = explode('.', $groupKey)[0]; 
                                        $isExpanded = in_array($groupKey, $expanded_permission_groups) || $permission_search !== '';
                                    @endphp
                                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                                        {{-- Module Header --}}
                                        <div class="flex items-center justify-between px-4 py-3 bg-slate-50 cursor-pointer hover:bg-slate-100 transition-colors"
                                            wire:click="togglePermissionGroup('{{ $groupKey }}')">
                                            <div class="flex items-center gap-3">
                                                <span class="text-lg">{{ $groupIcons[$iconKey] ?? '📋' }}</span>
                                                <div>
                                                    <div class="text-sm font-medium text-slate-900">{{ $group['label'] }}</div>
                                                    <div class="text-xs text-slate-500">{{ $group['selected'] }}/{{ $group['total'] }} selected</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="w-20 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                                    <div class="h-full bg-primary-blue rounded-full" style="width: {{ $group['percentage'] }}%"></div>
                                                </div>
                                                <button type="button" 
                                                    wire:click.stop="toggleAllForModule('{{ $groupKey }}')"
                                                    class="px-2 py-1 text-xs font-medium rounded transition-colors {{ $group['all_selected'] ? 'bg-primary-blue text-white' : 'bg-slate-200 text-slate-600 hover:bg-slate-300' }}">
                                                    {{ $group['all_selected'] ? __('All') : __('All') }}
                                                </button>
                                                <svg class="w-4 h-4 text-slate-400 transition-transform {{ $isExpanded ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        {{-- Permission Checkboxes --}}
                                        @if ($isExpanded)
                                            <div class="p-4 bg-white border-t border-slate-200">
                                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                                    @foreach ($group['permissions'] as $perm)
                                                        @php
                                                            $parts = explode('.', $perm->name);
                                                            $action = end($parts);
                                                            $isSelected = in_array($perm->name, $selected_permissions);
                                                        @endphp
                                                        <label class="flex items-center gap-2 p-2 rounded-md hover:bg-slate-50 cursor-pointer transition-colors {{ $isSelected ? 'bg-blue-50' : '' }}">
                                                            <input type="checkbox" 
                                                                value="{{ $perm->name }}" 
                                                                wire:click="togglePermission('{{ $perm->name }}')"
                                                                {{ $isSelected ? 'checked' : '' }}
                                                                class="w-4 h-4 text-primary-blue border-slate-300 rounded focus:ring-primary-blue cursor-pointer" />
                                                            <span class="text-sm {{ $isSelected ? 'text-primary-blue font-medium' : 'text-slate-700' }}">
                                                                {{ ucfirst($action) }}
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        @error('selected_permissions') <div class="mt-2 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                        @if ($editing_role_id)
                            <button type="button" wire:click="closeEditModal" class="ui-btn-secondary w-full sm:w-auto">
                                {{ __('Cancel') }}
                            </button>
                        @endif
                        <button type="button" wire:click="save" class="ui-btn-primary w-full sm:w-auto">
                            {{ $editing_role_id ? __('Update Role') : __('Create Role') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- Existing Roles List --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Existing Roles') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Role Name') }}</th>
                                        <th>{{ __('Permissions') }}</th>
                                        <th class="text-right">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $role)
                                        <tr wire:key="role-{{ $role->id }}">
                                            <td class="font-medium text-slate-900">
                                                <button type="button" 
                                                    wire:click="toggleRolePermissions({{ $role->id }})" 
                                                    class="flex items-center gap-2 hover:text-primary-blue transition-colors">
                                                    <svg class="w-4 h-4 text-slate-400 transition-transform {{ $expanded_role_id === $role->id ? 'rotate-90' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                    {{ $role->name }}
                                                </button>
                                            </td>
                                            <td class="text-slate-700">
                                                @if ($expanded_role_id === $role->id)
                                                    <div class="flex flex-wrap gap-1 max-w-lg">
                                                        @foreach ($role->permissions()->orderBy('name')->get() as $perm)
                                                            <span class="inline-block px-2 py-0.5 text-xs bg-primary-blue/10 text-primary-blue rounded">{{ $perm->name }}</span>
                                                        @endforeach
                                                        @if ($role->permissions()->count() === 0)
                                                            <span class="text-xs text-slate-400 italic">{{ __('No permissions') }}</span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-xs text-slate-500">
                                                        {{ $role->permissions()->count() }} {{ __('permissions') }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="inline-flex items-center gap-2">
                                                    <button type="button" wire:click="openEditModal({{ $role->id }})" class="ui-btn-link">{{ __('Edit') }}</button>
                                                    <button type="button" wire:click="openDeleteModal({{ $role->id }})" class="ui-btn-link-danger">{{ __('Delete') }}</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if ($roles->isEmpty())
                                        <tr>
                                            <td colspan="3" class="text-center text-sm text-slate-500 py-8">{{ __('No roles found for this branch.') }}</td>
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

    {{-- Delete Confirmation Modal --}}
    @if ($show_delete_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeDeleteModal" data-modal-overlay></div>
            <div class="relative w-full max-w-md ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="text-sm text-slate-500">{{ __('Confirm Delete') }}</div>
                    <div class="mt-1 font-semibold text-slate-900">{{ __('Delete Role') }}</div>
                </div>

                <div class="p-4">
                    <div class="text-sm text-slate-700">
                        {{ __('Are you sure you want to delete the role') }} <span class="font-semibold text-slate-900">{{ $pending_delete_name ?: '-' }}</span>?
                        <p class="mt-2 text-xs text-slate-500">{{ __('This action cannot be undone.') }}</p>
                    </div>

                    <div class="mt-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                        <button type="button" wire:click="closeDeleteModal" class="ui-btn-secondary w-full sm:w-auto" data-modal-close>{{ __('Cancel') }}</button>
                        <button type="button" wire:click="confirmDelete" class="ui-btn-danger w-full sm:w-auto">{{ __('Delete') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
