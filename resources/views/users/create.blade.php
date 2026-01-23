<x-app-layout>
    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Create Branch Admin') }}
                </h2>
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

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Full Name') }}</label>
                            <input type="text" name="name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                            @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                            @error('email') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Branch') }}</label>
                            <select name="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
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
                            <label class="block text-sm font-medium text-gray-700">{{ __('Password') }}</label>
                            <input type="password" name="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                            @error('password') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ __('Confirm Password') }}</label>
                            <input type="password" name="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required />
                            @error('password_confirmation') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm text-white hover:bg-indigo-700">
                                {{ __('Create User') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
