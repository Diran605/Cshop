<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="font-semibold text-xl text-slate-900 leading-tight">
                {{ __('Branch Admins') }}
            </h2>
        </div>

        @if (session('status'))
            <div class="ui-alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('temp_password'))
            <div class="ui-alert-warning">
                {{ __('Temporary Password:') }}
                <span class="font-semibold">{{ session('temp_password') }}</span>
            </div>
        @endif

        @if (session('warning'))
            <div class="ui-alert-warning">
                {{ session('warning') }}
            </div>
        @endif

        @if (session('error'))
            <div class="ui-alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="space-y-6">
            @if (! $show_edit_modal)
                <div class="ui-card">
                    <div class="ui-card-body">
                        <h3 class="ui-card-title">
                            {{ __('Add Branch Admin') }}
                        </h3>

                        <div class="mt-4 space-y-4">
                        <div>
                            <label class="ui-label">{{ __('Full Name') }}</label>
                            <input type="text" wire:model.defer="name" class="mt-1 ui-input" />
                            @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Email') }}</label>
                            <input type="email" wire:model.defer="email" class="mt-1 ui-input" />
                            @error('email') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Branch') }}</label>
                            <select wire:model.defer="branch_id" class="mt-1 ui-select">
                                <option value="0">{{ __('Select...') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <button type="button" wire:click="save" class="ui-btn-primary">
                                {{ __('Save') }}
                            </button>
                        </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="flex items-center justify-between">
                        <h3 class="ui-card-title">{{ __('All Branch Admins') }}</h3>
                        <div class="w-64">
                            <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('Search...') }}" class="ui-input" />
                        </div>
                    </div>

                    <div class="mt-4 overflow-x-auto">
                        <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Name') }}</th>
                                    <th>{{ __('Email') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr wire:key="user-{{ $user->id }}">
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->branch?->name ?? '-' }}</td>
                                        <td class="text-right">
                                            <div class="inline-flex items-center gap-3">
                                                <button type="button" wire:click.stop.prevent="openEditModal({{ $user->id }})" class="ui-btn-link">{{ __('Edit') }}</button>
                                                <button type="button" wire:click.stop.prevent="openDeleteModal({{ $user->id }})" class="ui-btn-link-danger">
                                                    {{ __('Delete') }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($users->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center text-sm text-slate-500">{{ __('No users found.') }}</td>
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
                <div class="p-4 border-b border-gray-200">
                    <div class="text-sm text-gray-500">{{ __('Confirm Delete') }}</div>
                    <div class="mt-1 font-semibold text-gray-900">{{ __('Delete User') }}</div>
                </div>

                <div class="p-4">
                    <div class="text-sm text-gray-700">
                        {{ __('Are you sure you want to delete this user?') }}
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
                <div class="p-4 border-b border-gray-200 flex items-center justify-between">
                    <div>
                        <div class="text-sm text-gray-500">{{ __('Edit Branch Admin') }}</div>
                        <div class="mt-1 font-semibold text-gray-900">{{ $name ?: '-' }}</div>
                    </div>
                    <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                </div>

                <div class="p-4">
                    <div class="space-y-4">
                        <div>
                            <label class="ui-label">{{ __('Full Name') }}</label>
                            <input type="text" wire:model.defer="name" class="mt-1 ui-input" />
                            @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Email') }}</label>
                            <input type="email" wire:model.defer="email" class="mt-1 ui-input" />
                            @error('email') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Branch') }}</label>
                            <select wire:model.defer="branch_id" class="mt-1 ui-select">
                                <option value="0">{{ __('Select...') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="ui-label">{{ __('Password') }}</label>
                                <input type="password" wire:model.defer="password" class="mt-1 ui-input" />
                                @error('password') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Confirm Password') }}</label>
                                <input type="password" wire:model.defer="password_confirmation" class="mt-1 ui-input" />
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
