<div x-data="{ 
    open: false,
    init() {
        // Close dropdown when Livewire navigates
        document.addEventListener('livewire:navigating', () => {
            this.open = false;
        });
    }
}" class="relative">
    <button @click="open = !open" type="button" class="relative p-2 rounded-lg hover:bg-white/10 transition" title="{{ __('Notifications') }}">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        @if ($unreadCount > 0)
            <span class="absolute top-1 right-1 flex h-5 w-5 items-center justify-center rounded-full bg-error text-xs font-bold text-white">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown -->
    <div x-show="open" style="display: none;"
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-slate-200 z-50">
        
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-slate-900">{{ __('Notifications') }}</h3>
                @if ($unreadCount > 0)
                    <p class="text-xs text-slate-500 mt-1">{{ $unreadCount }} {{ __('unread') }}</p>
                @else
                    <p class="text-xs text-slate-500 mt-1">{{ __('No new notifications') }}</p>
                @endif
            </div>
            <a href="{{ route('notifications.index') }}" class="text-xs text-primary-blue hover:underline">{{ __('View All') }}</a>
        </div>

        <div class="max-h-96 overflow-y-auto">
            @if ($notifications->count() > 0)
                @foreach ($notifications as $notification)
                    <a href="{{ route('notifications.index') }}#{{ $notification->type }}" class="block p-4 hover:bg-slate-50 border-b border-slate-100 last:border-b-0 {{ ! $notification->read_at ? 'bg-blue-50/50' : '' }}">
                        <div class="flex items-start gap-3">
                            <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $notification->type === 'low_stock' ? 'bg-red-100' : ($notification->type === 'expiry_warning' ? 'bg-amber-100' : 'bg-blue-100') }}">
                                @if ($notification->type === 'low_stock')
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                                    </svg>
                                @elseif ($notification->type === 'expiry_warning')
                                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                @endif
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-slate-900">{{ $notification->title }}</p>
                                <p class="text-xs text-slate-600 mt-0.5">{{ $notification->message }}</p>
                                <p class="text-xs text-slate-400 mt-1">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                            @if (! $notification->read_at)
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-1 flex-shrink-0"></div>
                            @endif
                        </div>
                    </a>
                @endforeach
            @else
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-sm text-slate-500">{{ __('No notifications') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>
