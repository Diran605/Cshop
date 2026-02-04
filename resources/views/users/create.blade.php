<x-app-layout>
    <div class="ui-page">
        <div class="ui-page-container">
            <div class="mb-6">
                <h2 class="ui-page-title">{{ __('Create Branch Admin') }}</h2>
                <div class="ui-page-subtitle">{{ __('Add a new branch admin user.') }}</div>
            </div>

            @if (session('status'))
                <div class="ui-alert-success">
                    {{ session('status') }}
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

            <div class="ui-card">
                <div class="ui-card-body">
                    <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="ui-label">{{ __('Full Name') }}</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="mt-1 ui-input" required />
                            @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Email') }}</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="mt-1 ui-input" required />
                            @error('email') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Branch') }}</label>
                            <select name="branch_id" class="mt-1 ui-select" required>
                                <option value="">{{ __('Select...') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" @selected((string) old('branch_id') === (string) $branch->id)>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('branch_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Password') }}</label>
                            <input type="password" name="password" class="mt-1 ui-input" required />
                            @error('password') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Confirm Password') }}</label>
                            <input type="password" name="password_confirmation" class="mt-1 ui-input" required />
                            @error('password_confirmation') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit" class="ui-btn-primary">
                                {{ __('Create User') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
