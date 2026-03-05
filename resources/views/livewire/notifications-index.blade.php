<div class="ui-page">
    <div class="ui-page-container">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="ui-page-title">{{ __('Notifications & Alerts') }}</h2>
                <div class="ui-page-subtitle">{{ __('View low stock alerts, expiry warnings, and system notifications.') }}</div>
            </div>
            @if ($this->isSuperAdmin)
                <div class="flex items-center gap-2">
                    <label class="text-sm text-slate-600">{{ __('Branch:') }}</label>
                    <select wire:model.live="filter_branch_id" class="ui-input w-48">
                        <option value="0">{{ __('All Branches') }}</option>
                        @foreach ($this->branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
        </div>

        {{-- Stats Cards --}}
        <div class="mb-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <div class="text-2xl font-bold text-slate-900">{{ $this->stats['unread'] }}</div>
                <div class="text-sm text-slate-500">{{ __('Unread') }}</div>
            </div>
            <div class="bg-red-50 rounded-xl border border-red-200 p-4">
                <div class="text-2xl font-bold text-red-600">{{ $this->stats['low_stock'] }}</div>
                <div class="text-sm text-red-600">{{ __('Low Stock') }}</div>
            </div>
            <div class="bg-amber-50 rounded-xl border border-amber-200 p-4">
                <div class="text-2xl font-bold text-amber-600">{{ $this->stats['expiring'] }}</div>
                <div class="text-sm text-amber-600">{{ __('Expiring Soon') }}</div>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-4">
                <div class="text-2xl font-bold text-slate-900">{{ $this->stats['total'] }}</div>
                <div class="text-sm text-slate-500">{{ __('Total Alerts') }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Low Stock Alerts Panel --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('Low Stock Items') }}</h3>
                            <p class="text-xs text-slate-500">{{ __('Items below minimum stock level') }}</p>
                        </div>
                    </div>

                    @if ($this->low_stock_alerts->count() > 0)
                        <div class="space-y-2 max-h-80 overflow-y-auto">
                            @foreach ($this->low_stock_alerts as $stock)
                                <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-100">
                                    <div>
                                        <div class="text-sm font-medium text-slate-900">{{ $stock->product?->name ?? '-' }}</div>
                                        <div class="text-xs text-slate-500">
                                            {{ __('Min:') }} {{ $stock->minimum_stock }} | {{ __('Current:') }} {{ $stock->current_stock }}
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-bold text-red-600">{{ $stock->current_stock }}</div>
                                        <div class="text-xs text-slate-500">{{ __('units left') }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-green-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm text-slate-500">{{ __('All items are well stocked') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Expiry Alerts Panel --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">{{ __('Expiring Products') }}</h3>
                            <p class="text-xs text-slate-500">{{ __('Items expiring within 30 days') }}</p>
                        </div>
                    </div>

                    @if ($this->expiry_alerts->count() > 0)
                        <div class="space-y-2 max-h-80 overflow-y-auto">
                            @php
                                $expired = 0;
                                $expiring7 = 0;
                                $expiring30 = 0;
                            @endphp
                            @foreach ($this->expiry_alerts as $item)
                                @php
                                    $daysUntil = Carbon\Carbon::parse($item->expiry_date)->diffInDays(now(), false);
                                    $isExpired = $daysUntil > 0;
                                    $isExpiring7 = !$isExpired && abs($daysUntil) <= 7;
                                    if ($isExpired) $expired++;
                                    elseif ($isExpiring7) $expiring7++;
                                    else $expiring30++;
                                @endphp
                                <div class="flex items-center justify-between p-3 rounded-lg border {{ $isExpired ? 'bg-red-100 border-red-200' : ($isExpiring7 ? 'bg-amber-50 border-amber-200' : 'bg-yellow-50 border-yellow-200') }}">
                                    <div>
                                        <div class="text-sm font-medium text-slate-900">{{ $item->product_name }}</div>
                                        <div class="text-xs text-slate-500">{{ $item->remaining_quantity }} {{ __('units') }}</div>
                                    </div>
                                    <div class="text-right">
                                        @if ($isExpired)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-200 text-red-800">
                                                {{ __('EXPIRED') }}
                                            </span>
                                        @else
                                            <span class="text-xs font-medium {{ $isExpiring7 ? 'text-amber-600' : 'text-yellow-600' }}">
                                                {{ Carbon\Carbon::parse($item->expiry_date)->diffForHumans() }}
                                            </span>
                                        @endif
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $item->expiry_date }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-green-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm text-slate-500">{{ __('No products expiring soon') }}</div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- System Notifications Panel --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-slate-900">{{ __('Notifications') }}</h3>
                                <p class="text-xs text-slate-500">{{ __('System alerts and messages') }}</p>
                            </div>
                        </div>
                        @if ($this->stats['unread'] > 0)
                            <button type="button" wire:click="markAllAsRead" class="text-xs text-primary-blue hover:underline">
                                {{ __('Mark all read') }}
                            </button>
                        @endif
                    </div>

                    {{-- Filter Tabs --}}
                    <div class="flex gap-2 mb-4 border-b border-slate-200">
                        <button type="button" wire:click="$set('filter', 'all')" class="px-3 py-2 text-sm font-medium {{ $filter === 'all' ? 'text-primary-blue border-b-2 border-primary-blue' : 'text-slate-500 hover:text-slate-700' }}">
                            {{ __('All') }}
                        </button>
                        <button type="button" wire:click="$set('filter', 'low_stock')" class="px-3 py-2 text-sm font-medium {{ $filter === 'low_stock' ? 'text-primary-blue border-b-2 border-primary-blue' : 'text-slate-500 hover:text-slate-700' }}">
                            {{ __('Low Stock') }}
                        </button>
                        <button type="button" wire:click="$set('filter', 'expiry_warning')" class="px-3 py-2 text-sm font-medium {{ $filter === 'expiry_warning' ? 'text-primary-blue border-b-2 border-primary-blue' : 'text-slate-500 hover:text-slate-700' }}">
                            {{ __('Expiry') }}
                        </button>
                    </div>

                    @if ($this->alerts->count() > 0)
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @foreach ($this->alerts as $alert)
                                <div class="flex items-start gap-3 p-3 rounded-lg {{ $alert->is_read ? 'bg-slate-50' : 'bg-blue-50 border border-blue-100' }}">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center {{ $alert->type === 'low_stock' ? 'bg-red-100' : ($alert->type === 'expiry_warning' ? 'bg-amber-100' : 'bg-blue-100') }}">
                                        @if ($alert->type === 'low_stock')
                                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                                            </svg>
                                        @elseif ($alert->type === 'expiry_warning')
                                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @else
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-medium text-slate-900">{{ $alert->title }}</div>
                                        <div class="text-xs text-slate-500 mt-0.5">{{ $alert->message }}</div>
                                        <div class="text-xs text-slate-400 mt-1">{{ $alert->created_at->diffForHumans() }}</div>
                                    </div>
                                    @if (! $alert->is_read)
                                        <button type="button" wire:click="markAsRead({{ $alert->id }})" class="text-xs text-primary-blue hover:underline whitespace-nowrap">
                                            {{ __('Mark read') }}
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if ($this->alerts->hasPages())
                            <div class="mt-4">
                                {{ $this->alerts->links('pagination::tailwind') }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-slate-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                            </svg>
                            <div class="text-sm text-slate-500">{{ __('No notifications') }}</div>
                        </div>
                    @endif

                    @if ($this->stats['total'] > 0)
                        <div class="mt-4 pt-4 border-t border-slate-200">
                            <button type="button" wire:click="deleteRead" wire:confirm="{{ __('Delete all read notifications?') }}" class="text-xs text-slate-500 hover:text-red-600">
                                {{ __('Delete read notifications') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
