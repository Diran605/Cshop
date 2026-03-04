<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="ui-page-title">
                {{ __('Branch Dashboard') }}
            </h2>
            <div class="ui-page-subtitle">
                @if (auth()->user() && auth()->user()->branch)
                    {{ __('Branch:') }}
                    <span class="font-medium">{{ auth()->user()->branch->name }}</span>
                @endif
            </div>
        </div>
    </x-slot>

    <livewire:branch-dashboard />
</x-app-layout>
