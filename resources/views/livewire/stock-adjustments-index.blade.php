<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Stock Adjustments') }}</h2>
            <div class="ui-page-subtitle">{{ __('Review and approve stock adjustments including product voids') }}</div>
        </div>

        @if (session('status'))
            <div class="ui-alert-success">{{ session('status') }}</div>
        @endif

        <div class="ui-card">
            <div class="ui-card-body">
                    {{-- Mode Tabs --}}
                    <div class="flex items-center gap-4 mb-6">
                        <button type="button" wire:click="setMode('pending')" class="{{ $mode === 'pending' ? 'ui-btn-primary' : 'ui-btn-secondary' }}">
                            {{ __('Pending Approval') }}
                            @if ($mode === 'pending')
                                <span class="ml-2 px-2 py-0.5 bg-white/20 rounded-full text-xs">{{ $adjustments->total() }}</span>
                            @endif
                        </button>
                        <button type="button" wire:click="setMode('history')" class="{{ $mode === 'history' ? 'ui-btn-primary' : 'ui-btn-secondary' }}">
                            {{ __('History') }}
                        </button>
                    </div>

                    {{-- Filters --}}
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
                        @if ($isSuperAdmin)
                            <div>
                                <label class="ui-label">{{ __('Branch') }}</label>
                                <select wire:model.live="branch_id" class="mt-1 ui-select">
                                    <option value="0">{{ __('All Branches') }}</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div>
                            <label class="ui-label">{{ __('Status') }}</label>
                            <select wire:model.live="status_filter" class="mt-1 ui-select">
                                <option value="all">{{ __('All Statuses') }}</option>
                                <option value="pending">{{ __('Pending') }}</option>
                                <option value="approved">{{ __('Approved') }}</option>
                                <option value="rejected">{{ __('Rejected') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="ui-label">{{ __('Type') }}</label>
                            <select wire:model.live="type_filter" class="mt-1 ui-select">
                                <option value="all">{{ __('All Types') }}</option>
                                <option value="void_product">{{ __('Product Void') }}</option>
                                <option value="stock_in_void">{{ __('Stock In Void') }}</option>
                                <option value="sales_void">{{ __('Sales Void') }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="ui-label">{{ __('From') }}</label>
                            <input type="date" wire:model.live="date_from" class="mt-1 ui-input" />
                        </div>
                        <div>
                            <label class="ui-label">{{ __('To') }}</label>
                            <input type="date" wire:model.live="date_to" class="mt-1 ui-input" />
                        </div>
                        <div>
                            <label class="ui-label">{{ __('Search Product') }}</label>
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Product name...') }}" class="mt-1 ui-input" />
                        </div>
                    </div>

                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    @if ($isSuperAdmin)
                                        <th>{{ __('Branch') }}</th>
                                    @endif
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Type') }}</th>
                                    <th class="text-center">{{ __('Current') }}</th>
                                    <th class="text-center">{{ __('Adjustment') }}</th>
                                    <th class="text-center">{{ __('Target') }}</th>
                                    <th class="text-center">{{ __('Status') }}</th>
                                    <th>{{ __('Requested By') }}</th>
                                    <th class="text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($adjustments as $adjustment)
                                    <tr wire:key="adjustment-{{ $adjustment->id }}" class="{{ $adjustment->isPending() ? 'bg-yellow-50' : '' }}">
                                        <td class="whitespace-nowrap">
                                            <div>{{ $adjustment->created_at->format('M j, Y') }}</div>
                                            <div class="text-xs text-slate-500">{{ $adjustment->created_at->format('H:i') }}</div>
                                        </td>
                                        @if ($isSuperAdmin)
                                            <td class="whitespace-nowrap">{{ $adjustment->branch?->name ?? '-' }}</td>
                                        @endif
                                        <td class="whitespace-nowrap">
                                            <div class="font-medium">{{ $adjustment->product?->name ?? '-' }}</div>
                                            @if ($adjustment->product?->isVoidPending())
                                                <span class="text-xs text-orange-600">{{ __('Void Pending') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $typeLabels = [
                                                    'void_product' => 'Product Void',
                                                    'stock_in_void' => 'Stock In Void',
                                                    'sales_void' => 'Sales Void',
                                                ];
                                            @endphp
                                            <span class="ui-badge-warning">
                                                {{ __($typeLabels[$adjustment->adjustment_type] ?? $adjustment->adjustment_type) }}
                                            </span>
                                        </td>
                                        <td class="text-center font-mono">{{ $adjustment->current_stock }}</td>
                                        <td class="text-center font-mono {{ $adjustment->adjustment_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $adjustment->adjustment_quantity > 0 ? '+' : '' }}{{ $adjustment->adjustment_quantity }}
                                        </td>
                                        <td class="text-center font-mono font-medium">{{ $adjustment->target_stock }}</td>
                                        <td class="text-center">
                                            @if ($adjustment->isPending())
                                                <span class="ui-badge-warning">{{ __('Pending') }}</span>
                                            @elseif ($adjustment->isApproved())
                                                <span class="ui-badge-success">{{ __('Approved') }}</span>
                                            @else
                                                <span class="ui-badge-danger">{{ __('Rejected') }}</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap">
                                            <div>{{ $adjustment->requester?->name ?? '-' }}</div>
                                            @if ($adjustment->reviewer)
                                                <div class="text-xs text-slate-500">
                                                    {{ $adjustment->reviewer->name }} • {{ $adjustment->reviewed_at?->format('M j') }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="inline-flex items-center gap-2">
                                                <button type="button" wire:click="viewAdjustment({{ $adjustment->id }})" class="ui-btn-link text-xs">{{ __('View') }}</button>
                                                @if ($adjustment->isPending())
                                                    @can('stock_adjustments.approve')
                                                        <button type="button" wire:click="openApproveModal({{ $adjustment->id }})" class="px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 font-medium">{{ __('Approve') }}</button>
                                                    @endcan
                                                    @can('stock_adjustments.reject')
                                                        <button type="button" wire:click="openRejectModal({{ $adjustment->id }})" class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 font-medium">{{ __('Reject') }}</button>
                                                    @endcan
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isSuperAdmin ? 10 : 9 }}" class="ui-table-empty">
                                            {{ $mode === 'pending' ? __('No pending adjustments.') : __('No adjustments found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($adjustments->hasPages())
                        <div class="mt-4 ui-card-footer">
                            {{ $adjustments->links() }}
                        </div>
                    @endif
            </div>
        </div>

        {{-- View Modal --}}
        @if ($show_view_modal && $viewing_adjustment)
        <div wire:key="view-modal-{{ $viewing_adjustment->id }}" class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeViewModal" data-modal-overlay></div>
            <div class="relative w-full max-w-2xl ui-card max-h-[90vh] overflow-y-auto">
                <div class="p-4 border-b border-slate-200 sticky top-0 bg-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Adjustment Details') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">#{{ $viewing_adjustment->id }}</div>
                        </div>
                        <button type="button" wire:click="closeViewModal" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Branch') }}</div>
                            <div class="font-medium">{{ $viewing_adjustment->branch?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Product') }}</div>
                            <div class="font-medium">{{ $viewing_adjustment->product?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Type') }}</div>
                            <div class="font-medium">{{ ucfirst(str_replace('_', ' ', $viewing_adjustment->adjustment_type)) }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Status') }}</div>
                            <div class="font-medium">{{ ucfirst($viewing_adjustment->status) }}</div>
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-lg p-4">
                        <div class="text-xs text-slate-500 mb-2">{{ __('Stock Changes') }}</div>
                        <div class="flex items-center justify-between">
                            <div class="text-center">
                                <div class="text-2xl font-mono">{{ $viewing_adjustment->current_stock }}</div>
                                <div class="text-xs text-slate-500">{{ __('Current') }}</div>
                            </div>
                            <div class="text-2xl text-slate-400">→</div>
                            <div class="text-center">
                                <div class="text-2xl font-mono font-bold {{ $viewing_adjustment->target_stock > $viewing_adjustment->current_stock ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $viewing_adjustment->target_stock }}
                                </div>
                                <div class="text-xs text-slate-500">{{ __('Target') }}</div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-slate-500">{{ __('Reason') }}</div>
                        <div class="mt-1 text-sm bg-slate-50 rounded p-3">{{ $viewing_adjustment->reason ?? '-' }}</div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Requested By') }}</div>
                            <div class="font-medium">{{ $viewing_adjustment->requester?->name ?? '-' }}</div>
                            <div class="text-xs text-slate-500">{{ $viewing_adjustment->created_at->format('M j, Y H:i') }}</div>
                        </div>
                        @if ($viewing_adjustment->reviewer)
                            <div>
                                <div class="text-xs text-slate-500">{{ __('Reviewed By') }}</div>
                                <div class="font-medium">{{ $viewing_adjustment->reviewer->name }}</div>
                                <div class="text-xs text-slate-500">{{ $viewing_adjustment->reviewed_at?->format('M j, Y H:i') }}</div>
                            </div>
                        @endif
                    </div>

                    @if ($viewing_adjustment->rejection_reason)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                            <div class="text-xs text-red-600 font-medium">{{ __('Rejection Reason') }}</div>
                            <div class="mt-1 text-sm text-red-800">{{ $viewing_adjustment->rejection_reason }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Approve Modal --}}
    @if ($show_approve_modal)
        <div wire:key="approve-modal-{{ $pending_approve_id }}" class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeApproveModal" data-modal-overlay></div>
            <div class="relative w-full max-w-md ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <div class="text-lg font-semibold text-green-600">{{ __('Approve Adjustment') }}</div>
                        <button type="button" wire:click="closeApproveModal" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-4">
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-green-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm text-green-800">
                                @if ($viewing_adjustment?->adjustment_type === 'sales_void')
                                    {{ __('Approving will restore stock to inventory and mark the sale as voided.') }}
                                @elseif ($viewing_adjustment?->adjustment_type === 'stock_in_void')
                                    {{ __('Approving will reverse stock additions and mark the stock-in receipt as voided.') }}
                                @else
                                    {{ __('Approving this adjustment will update the stock and, if this is a product void, mark the product as voided.') }}
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($viewing_adjustment)
                        <div class="bg-slate-50 rounded-lg p-3 mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs text-slate-500">{{ __('Product') }}</div>
                                    <div class="font-medium">{{ $viewing_adjustment->product?->name ?? '-' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-slate-500">{{ __('Adjustment') }}</div>
                                    <div class="font-mono {{ $viewing_adjustment->adjustment_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $viewing_adjustment->adjustment_quantity > 0 ? '+' : '' }}{{ $viewing_adjustment->adjustment_quantity }}
                                    </div>
                                </div>
                            </div>
                            @if ($viewing_adjustment->reason)
                                <div class="mt-2 pt-2 border-t border-slate-200">
                                    <div class="text-xs text-slate-500">{{ __('Reason') }}: {{ $viewing_adjustment->reason }}</div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="text-sm text-slate-700 mb-4">
                        {{ __('Are you sure you want to approve this adjustment?') }}
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-end gap-3">
                    <button type="button" wire:click="closeApproveModal" class="ui-btn-secondary">{{ __('Cancel') }}</button>
                    <button type="button" wire:click="confirmApprove" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium">
                        {{ __('Approve') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Reject Modal --}}
    @if ($show_reject_modal)
        <div wire:key="reject-modal-{{ $pending_reject_id }}" class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeRejectModal" data-modal-overlay></div>
            <div class="relative w-full max-w-md ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <div class="text-lg font-semibold text-red-600">{{ __('Reject Adjustment') }}</div>
                        <button type="button" wire:click="closeRejectModal" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-4">
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="text-sm text-red-800">
                                @if ($viewing_adjustment?->adjustment_type === 'sales_void')
                                    {{ __('Rejecting will cancel the void request and the sale will remain active.') }}
                                @elseif ($viewing_adjustment?->adjustment_type === 'stock_in_void')
                                    {{ __('Rejecting will cancel the void request and the stock-in receipt will remain active.') }}
                                @else
                                    {{ __('Rejecting this adjustment will discard the stock change. If this is a product void request, the product will return to active status.') }}
                                @endif
                            </div>
                        </div>
                    </div>

                    @if ($viewing_adjustment)
                        <div class="bg-slate-50 rounded-lg p-3 mb-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs text-slate-500">{{ __('Product') }}</div>
                                    <div class="font-medium">{{ $viewing_adjustment->product?->name ?? '-' }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs text-slate-500">{{ __('Adjustment') }}</div>
                                    <div class="font-mono {{ $viewing_adjustment->adjustment_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $viewing_adjustment->adjustment_quantity > 0 ? '+' : '' }}{{ $viewing_adjustment->adjustment_quantity }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="ui-label">{{ __('Rejection Reason (required)') }}</label>
                        <textarea wire:model.live="rejection_reason" rows="3" class="mt-1 ui-input" placeholder="{{ __('Enter reason for rejection...') }}"></textarea>
                        @error('rejection_reason')
                            <div class="mt-1 text-sm text-red-600">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-end gap-3">
                    <button type="button" wire:click="closeRejectModal" class="ui-btn-secondary">{{ __('Cancel') }}</button>
                    <button type="button" wire:click="confirmReject" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-medium">
                        {{ __('Reject') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
</div>
