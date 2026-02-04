<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ui-page-title">{{ __('Profile') }}</h2>
            <div class="ui-page-subtitle">{{ __('Manage your account settings.') }}</div>
        </div>
    </x-slot>

    <div class="ui-page">
        <div class="ui-page-container space-y-6">
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
