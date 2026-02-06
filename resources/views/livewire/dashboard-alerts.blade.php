<div class="space-y-3">
    @forelse ($alerts as $alert)
        <div class="ui-card p-4 border-l-4 {{ $alert->is_read ? 'border-l-gray-200 opacity-60' : 'border-l-error' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold uppercase tracking-wider {{ $alert->type === 'stock_adjustment' ? 'text-warning' : ($alert->type === 'expired_stock' ? 'text-error' : ($alert->type === 'expiry_warning' ? 'text-warning' : 'text-info')) }}">
                            @if ($alert->type === 'stock_adjustment')
                                {{ __('Stock Adjustment') }}
                            @elseif ($alert->type === 'expired_stock')
                                {{ __('Expired Stock') }}
                            @elseif ($alert->type === 'expiry_warning')
                                {{ __('Expiry Warning') }}
                            @elseif ($alert->type === 'low_stock')
                                {{ __('Low Stock') }}
                            @endif
                        </span>
                        @if (!$alert->is_read)
                            <span class="w-2 h-2 rounded-full bg-error"></span>
                        @endif
                    </div>
                    <h4 class="mt-1 text-sm font-semibold text-text-primary">{{ $alert->title }}</h4>
                    <p class="mt-1 text-xs text-text-secondary">{{ $alert->message }}</p>
                    <p class="mt-2 text-xs text-text-disabled">{{ $alert->created_at->diffForHumans() }}</p>
                </div>
                @if (!$alert->is_read)
                    <button wire:click="markAsRead({{ $alert->id }})" class="text-xs font-medium text-primary-blue hover:text-deep-blue">
                        {{ __('Mark as read') }}
                    </button>
                @endif
            </div>
        </div>
    @empty
        <div class="ui-card p-6 text-center">
            <p class="text-sm text-text-secondary">{{ __('No alerts') }}</p>
        </div>
    @endforelse
</div>
