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
    </div>
</div>
