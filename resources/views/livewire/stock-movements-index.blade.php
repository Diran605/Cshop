<div>
    <div class="ui-page">
        <div class="ui-page-container">
            <div class="mb-6">
                <h2 class="ui-page-title">{{ __('Stock Movements') }}</h2>
                <div class="ui-page-subtitle">{{ __('Audit trail of inventory movements.') }}</div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                        <div>
                            <label class="ui-label">{{ __('From') }}</label>
                            <input type="date" wire:model.live="date_from" class="mt-1 ui-input" />
                        </div>

                        <div>
                            <label class="ui-label">{{ __('To') }}</label>
                            <input type="date" wire:model.live="date_to" class="mt-1 ui-input" />
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Branch') }}</label>
                            <select wire:model.live="branch_id" class="mt-1 ui-select" @if (! $isSuperAdmin) disabled @endif>
                                @if ($isSuperAdmin)
                                    <option value="0">{{ __('All') }}</option>
                                @endif
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Product') }}</label>
                            <select wire:model.live="product_id" class="mt-1 ui-select">
                                <option value="0">{{ __('All') }}</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Type') }}</label>
                            <select wire:model.live="movement_type" class="mt-1 ui-select">
                                <option value="all">{{ __('All') }}</option>
                                <option value="IN">{{ __('Stock In') }}</option>
                                <option value="OUT">{{ __('Stock Out') }}</option>
                                <option value="clearance_allocation">{{ __('Clearance Allocation') }}</option>
                                <option value="clearance_reversal">{{ __('Clearance Reversal') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Search') }}</label>
                            <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('Product / User / Notes...') }}" class="mt-1 ui-input" />
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto">
                        <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Branch') }}</th>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th class="text-right">{{ __('Qty') }}</th>
                                        <th class="text-right">{{ __('Before') }}</th>
                                        <th class="text-right">{{ __('After') }}</th>
                                        <th class="text-right">{{ __('Unit Cost') }}</th>
                                        <th class="text-right">{{ __('Unit Price') }}</th>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Ref') }}</th>
                                        <th>{{ __('Notes') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($movements as $m)
                                        <tr wire:key="mv-{{ $m->id }}">
                                            <td>{{ optional($m->moved_at)->format('Y-m-d H:i') }}</td>
                                            <td>{{ $m->branch?->name ?? '-' }}</td>
                                            <td class="text-slate-900">{{ $m->product?->name ?? '-' }}</td>
                                            <td>
                                                @php
                                                    $typeLabel = match($m->movement_type) {
                                                        'IN' => 'Stock In',
                                                        'OUT' => 'Stock Out',
                                                        'clearance_allocation' => 'Clearance',
                                                        'clearance_reversal' => 'Reversal',
                                                        default => $m->movement_type,
                                                    };
                                                    $typeBg = match($m->movement_type) {
                                                        'IN' => 'bg-green-100 text-green-800',
                                                        'OUT' => 'bg-red-100 text-red-800',
                                                        'clearance_allocation' => 'bg-amber-100 text-amber-800',
                                                        'clearance_reversal' => 'bg-purple-100 text-purple-800',
                                                        default => 'bg-slate-100 text-slate-800',
                                                    };
                                                @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeBg }}">{{ $typeLabel }}</span>
                                            </td>
                                            <td class="text-right text-slate-900">{{ (int) $m->quantity }}</td>
                                            <td class="text-right">{{ (int) $m->before_stock }}</td>
                                            <td class="text-right">{{ (int) $m->after_stock }}</td>
                                            <td class="text-right">{{ $m->unit_cost !== null ? number_format((float) $m->unit_cost, 2) : '-' }}</td>
                                            <td class="text-right">{{ $m->unit_price !== null ? number_format((float) $m->unit_price, 2) : '-' }}</td>
                                            <td>{{ $m->user?->name ?? '-' }}</td>
                                            <td>
                                                @if ($m->stock_in_receipt_id)
                                                    {{ __('SI') }} #{{ $m->stock_in_receipt_id }}
                                                @elseif ($m->sales_receipt_id)
                                                    {{ __('SL') }} #{{ $m->sales_receipt_id }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="max-w-xs truncate text-xs text-slate-500" title="{{ $m->notes }}">{{ Str::limit($m->notes, 40) }}</td>
                                            <td>
                                                <button wire:click="openDetailModal({{ $m->id }})" class="ui-btn-link">
                                                    {{ __('View') }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if ($movements->isEmpty())
                                        <tr>
                                            <td colspan="13" class="ui-table-empty">{{ __('No movements found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            </div>

                            @if (method_exists($movements, 'hasPages') && $movements->hasPages())
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="text-sm text-slate-600">
                                        {{ __('Showing') }} {{ $movements->firstItem() }} {{ __('to') }} {{ $movements->lastItem() }} {{ __('of') }} {{ $movements->total() }} {{ __('results') }}
                                    </div>
                                    {{ $movements->links('pagination::tailwind') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($show_detail_modal)
        <div class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-8 sm:pt-12 overflow-y-auto" data-modal-root>
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeDetailModal" data-modal-overlay></div>
            <div class="relative w-full max-w-4xl ui-card flex flex-col mb-4 z-10">
                <!-- Header -->
                <div class="p-6 border-b border-slate-200 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 {{ $selected_movement->movement_type === 'IN' ? 'bg-green-100' : 'bg-red-100' }} rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 {{ $selected_movement->movement_type === 'IN' ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if ($selected_movement->movement_type === 'IN')
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                @endif
                            </svg>
                        </div>
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Stock Movement Details') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">
                                {{ $selected_movement->movement_type }} - {{ $selected_movement->product?->name ?? '-' }}
                            </div>
                        </div>
                    </div>
                    <button type="button" wire:click="closeDetailModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                </div>

                <!-- Content -->
                <div class="p-6 overflow-y-auto flex-1 min-h-0">
                    @if ($selected_movement)
                        <!-- Key Information Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                            <!-- Date & Time -->
                            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-blue-600 font-medium">{{ __('Date & Time') }}</div>
                                        <div class="text-sm font-semibold text-blue-900">
                                            {{ optional($selected_movement->moved_at)->format('M j, Y H:i') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Branch -->
                            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-purple-600 font-medium">{{ __('Branch') }}</div>
                                        <div class="text-sm font-semibold text-purple-900">
                                            {{ $selected_movement->branch?->name ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- User -->
                            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-4 border border-green-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs text-green-600 font-medium">{{ __('User') }}</div>
                                        <div class="text-sm font-semibold text-green-900">
                                            {{ $selected_movement->user?->name ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Movement Type -->
                            <div class="bg-gradient-to-br {{ $selected_movement->movement_type === 'IN' ? 'from-green-50 to-green-100 border-green-200' : 'from-red-50 to-red-100 border-red-200' }} rounded-xl p-4 border">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 {{ $selected_movement->movement_type === 'IN' ? 'bg-green-500' : 'bg-red-500' }} rounded-lg flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            @if ($selected_movement->movement_type === 'IN')
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            @endif
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="text-xs {{ $selected_movement->movement_type === 'IN' ? 'text-green-600' : 'text-red-600' }} font-medium">{{ __('Type') }}</div>
                                        <div class="text-sm font-semibold {{ $selected_movement->movement_type === 'IN' ? 'text-green-900' : 'text-red-900' }}">
                                            {{ $selected_movement->movement_type }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                            <!-- Quantity -->
                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" />
                                    </svg>
                                    <label class="text-sm font-medium text-slate-700">{{ __('Quantity') }}</label>
                                </div>
                                <div class="p-3 bg-white rounded-lg border border-slate-300">
                                    <div class="text-2xl font-bold {{ $selected_movement->movement_type === 'IN' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $selected_movement->movement_type === 'IN' ? '+' : '-' }}{{ (int) $selected_movement->quantity }}
                                    </div>
                                </div>
                            </div>

                            <!-- Before Stock -->
                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <label class="text-sm font-medium text-slate-700">{{ __('Before Stock') }}</label>
                                </div>
                                <div class="p-3 bg-white rounded-lg border border-slate-300">
                                    <div class="text-2xl font-bold text-slate-900">
                                        {{ (int) $selected_movement->before_stock }}
                                    </div>
                                </div>
                            </div>

                            <!-- After Stock -->
                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <label class="text-sm font-medium text-slate-700">{{ __('After Stock') }}</label>
                                </div>
                                <div class="p-3 bg-white rounded-lg border border-slate-300">
                                    <div class="text-2xl font-bold text-slate-900">
                                        {{ (int) $selected_movement->after_stock }}
                                    </div>
                                </div>
                            </div>

                            <!-- Product -->
                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                    </svg>
                                    <label class="text-sm font-medium text-slate-700">{{ __('Product') }}</label>
                                </div>
                                <div class="p-3 bg-white rounded-lg border border-slate-300">
                                    <div class="text-sm font-medium text-slate-900">
                                        {{ $selected_movement->product?->name ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Financial Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <!-- Unit Cost -->
                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <label class="text-sm font-medium text-slate-700">{{ __('Unit Cost') }}</label>
                                </div>
                                <div class="p-3 bg-white rounded-lg border border-slate-300">
                                    <div class="text-xl font-bold text-slate-900">
                                        {{ $selected_movement->unit_cost !== null ? 'XAF ' . number_format((float) $selected_movement->unit_cost, 2) : '-' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Unit Price -->
                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <label class="text-sm font-medium text-slate-700">{{ __('Unit Price') }}</label>
                                </div>
                                <div class="p-3 bg-white rounded-lg border border-slate-300">
                                    <div class="text-xl font-bold text-slate-900">
                                        {{ $selected_movement->unit_price !== null ? 'XAF ' . number_format((float) $selected_movement->unit_price, 2) : '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reference Information -->
                        <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <label class="text-sm font-medium text-blue-700">{{ __('Reference Information') }}</label>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <div class="text-xs text-blue-600 mb-1">{{ __('Reference Type') }}</div>
                                    <div class="p-2 bg-white rounded border border-blue-300 text-sm font-medium">
                                        @if ($selected_movement->stock_in_receipt_id)
                                            {{ __('Stock In Receipt') }}
                                        @elseif ($selected_movement->sales_receipt_id)
                                            {{ __('Sales Receipt') }}
                                        @else
                                            {{ __('Manual Adjustment') }}
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-blue-600 mb-1">{{ __('Reference Number') }}</div>
                                    <div class="p-2 bg-white rounded border border-blue-300 text-sm font-mono">
                                        @if ($selected_movement->stock_in_receipt_id)
                                            SI #{{ $selected_movement->stock_in_receipt_id }}
                                        @elseif ($selected_movement->sales_receipt_id)
                                            SL #{{ $selected_movement->sales_receipt_id }}
                                        @else
                                            -
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <div class="text-xs text-blue-600 mb-1">{{ __('Movement ID') }}</div>
                                    <div class="p-2 bg-white rounded border border-blue-300 text-sm font-mono">
                                        #{{ $selected_movement->id }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Details -->
                        <div class="bg-amber-50 rounded-xl p-4 border border-amber-200 mt-6">
                            <div class="flex items-center gap-2 mb-3">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <label class="text-sm font-medium text-amber-700">{{ __('Complete Movement Details') }}</label>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- Movement Details -->
                                <div class="space-y-3">
                                    <div class="flex items-start gap-2">
                                        <div class="text-xs font-medium text-amber-700 min-w-24">{{ __('Movement Type') }}:</div>
                                        <div class="text-xs text-slate-700 flex-1">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs {{ $selected_movement->movement_type === 'IN' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $selected_movement->movement_type }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <div class="text-xs font-medium text-amber-700 min-w-24">{{ __('Quantity') }}:</div>
                                        <div class="text-xs text-slate-700 flex-1">
                                            <span class="font-mono {{ $selected_movement->movement_type === 'IN' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }} px-2 py-1 rounded">
                                                {{ $selected_movement->movement_type === 'IN' ? '+' : '-' }}{{ (int) $selected_movement->quantity }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <div class="text-xs font-medium text-amber-700 min-w-24">{{ __('Before Stock') }}:</div>
                                        <div class="text-xs text-slate-700 flex-1">
                                            <span class="font-mono bg-slate-50 text-slate-800 px-2 py-1 rounded">
                                                {{ (int) $selected_movement->before_stock }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <div class="text-xs font-medium text-amber-700 min-w-24">{{ __('After Stock') }}:</div>
                                        <div class="text-xs text-slate-700 flex-1">
                                            <span class="font-mono bg-slate-50 text-slate-800 px-2 py-1 rounded">
                                                {{ (int) $selected_movement->after_stock }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Financial Details -->
                                <div class="space-y-3">
                                    <div class="flex items-start gap-2">
                                        <div class="text-xs font-medium text-amber-700 min-w-24">{{ __('Unit Cost') }}:</div>
                                        <div class="text-xs text-slate-700 flex-1">
                                            @if ($selected_movement->unit_cost !== null)
                                                <span class="font-mono bg-green-50 text-green-800 px-2 py-1 rounded">
                                                    XAF {{ number_format((float) $selected_movement->unit_cost, 2) }}
                                                </span>
                                            @else
                                                <span class="text-slate-500 italic">{{ __('Not set') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <div class="text-xs font-medium text-amber-700 min-w-24">{{ __('Unit Price') }}:</div>
                                        <div class="text-xs text-slate-700 flex-1">
                                            @if ($selected_movement->unit_price !== null)
                                                <span class="font-mono bg-green-50 text-green-800 px-2 py-1 rounded">
                                                    XAF {{ number_format((float) $selected_movement->unit_price, 2) }}
                                                </span>
                                            @else
                                                <span class="text-slate-500 italic">{{ __('Not set') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <div class="text-xs font-medium text-amber-700 min-w-24">{{ __('Total Value') }}:</div>
                                        <div class="text-xs text-slate-700 flex-1">
                                            @if ($selected_movement->unit_cost !== null)
                                                <span class="font-mono bg-blue-50 text-blue-800 px-2 py-1 rounded">
                                                    XAF {{ number_format((float) $selected_movement->unit_cost * (int) $selected_movement->quantity, 2) }}
                                                </span>
                                            @else
                                                <span class="text-slate-500 italic">{{ __('Not calculable') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <div class="text-xs font-medium text-amber-700 min-w-24">{{ __('Movement ID') }}:</div>
                                        <div class="text-xs text-slate-700 flex-1">
                                            <span class="font-mono bg-slate-50 text-slate-800 px-2 py-1 rounded">
                                                #{{ $selected_movement->id }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($selected_movement->notes)
                                <div class="mt-4">
                                    <div class="text-xs text-amber-600 mb-2">{{ __('Notes') }}</div>
                                    <div class="p-3 bg-white rounded border border-amber-300">
                                        <div class="text-sm text-slate-900">
                                            {{ $selected_movement->notes }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Footer -->
                <div class="p-4 border-t border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 shrink-0">
                    <button type="button" wire:click="closeDetailModal" class="ui-btn-primary" data-modal-close>
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
</div>
