<div x-data="{ open: false }" class="relative">
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
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-75"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-slate-200 z-50">
        
        <div class="p-4 border-b border-slate-200">
            <h3 class="text-sm font-semibold text-slate-900">{{ __('Notifications') }}</h3>
            @if ($unreadCount > 0)
                <p class="text-xs text-slate-500 mt-1">{{ $unreadCount }} {{ __('unread') }}</p>
            @else
                <p class="text-xs text-slate-500 mt-1">{{ __('No new notifications') }}</p>
            @endif
        </div>

        <div class="max-h-96 overflow-y-auto">
            @php
                $notifications = App\Models\Alert::query()
                    ->forUser(auth()->id())
                    ->with(['user'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->get();
            @endphp

            @if ($notifications->count() > 0)
                @foreach ($notifications as $notification)
                    <div class="p-4 hover:bg-slate-50 border-b border-slate-100 last:border-b-0">
                        <div class="flex items-start gap-3">
                            <div class="flex-1">
                                <p class="text-sm text-slate-900">{{ $notification->message }}</p>
                                <p class="text-xs text-slate-500 mt-1">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                            @if (!$notification->read_at)
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-1"></div>
                            @endif
                        </div>
                    </div>
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
