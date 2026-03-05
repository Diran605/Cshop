<div>
<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Activity Logs') }}</h2>
            <div class="ui-page-subtitle">{{ __('Audit trail of system activities.') }}</div>
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
                        <label class="ui-label">{{ __('User') }}</label>
                        <select wire:model.live="user_id" class="mt-1 ui-select">
                            <option value="0">{{ __('All') }}</option>
                            @foreach ($users as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Action') }}</label>
                        <input type="text" wire:model.debounce.300ms="action" class="mt-1 ui-input" placeholder="e.g. user.created" />
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Search') }}</label>
                        <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('Description / Subject') }}" class="mt-1 ui-input" />
                    </div>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Branch') }}</th>
                                    <th>{{ __('Action') }}</th>
                                    <th>{{ __('Subject') }}</th>
                                    <th>{{ __('Description') }}</th>
                                    <th>{{ __('User') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($logs as $log)
                                    <tr wire:key="act-{{ $log->id }}">
                                        <td>{{ optional($log->created_at)->format('Y-m-d H:i') }}</td>
                                        <td>{{ $log->branch?->name ?? '-' }}</td>
                                        <td class="font-medium text-slate-900">{{ $log->action }}</td>
                                        <td class="text-slate-700">
                                            @php
                                                $st = $log->subject_type ? class_basename($log->subject_type) : null;
                                                $sid = $log->subject_id ? ('#' . $log->subject_id) : null;
                                            @endphp
                                            {{ $st ? ($st . ' ' . ($sid ?? '')) : '-' }}
                                        </td>
                                        <td>{{ $log->description ?? '-' }}</td>
                                        <td>{{ $log->user?->name ?? '-' }}</td>
                                        <td>
                                            <button wire:click="openDetailModal({{ $log->id }})" class="ui-btn-link">
                                                {{ __('View') }}
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach

                                @if ($logs->isEmpty())
                                    <tr>
                                        <td colspan="7" class="ui-table-empty">{{ __('No activity found.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    @if ($logs->hasPages())
                        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="text-sm text-slate-600">
                                {{ __('Showing') }} {{ $logs->firstItem() }} {{ __('to') }} {{ $logs->lastItem() }} {{ __('of') }} {{ $logs->total() }} {{ __('results') }}
                            </div>
                            {{ $logs->links('pagination::tailwind') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Detail Modal -->
@if ($show_detail_modal)
    <div class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-8 sm:pt-12 overflow-y-auto" data-modal-root>
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeDetailModal" data-modal-overlay></div>
        <div class="relative w-full max-w-3xl ui-card flex flex-col mb-4 z-10">
            <!-- Header -->
            <div class="p-6 border-b border-slate-200 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Activity Details') }}</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $selected_log->action ?? '-' }}</div>
                    </div>
                </div>
                <button type="button" wire:click="closeDetailModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
            </div>

            <!-- Content -->
            <div class="p-6 overflow-y-auto flex-1 min-h-0">
                @if ($selected_log)
                    <!-- Key Information Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
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
                                        {{ optional($selected_log->created_at)->format('M j, Y H:i') }}
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
                                        {{ $selected_log->branch?->name ?? '-' }}
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
                                        {{ $selected_log->user?->name ?? '-' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action & Subject -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <!-- Action -->
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                                <label class="text-sm font-medium text-slate-700">{{ __('Action') }}</label>
                            </div>
                            <div class="p-3 bg-white rounded-lg border border-slate-300">
                                <code class="text-sm text-slate-900 font-mono">{{ $selected_log->action }}</code>
                            </div>
                        </div>

                        <!-- Subject -->
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                            <div class="flex items-center gap-2 mb-2">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <label class="text-sm font-medium text-slate-700">{{ __('Subject') }}</label>
                            </div>
                            <div class="p-3 bg-white rounded-lg border border-slate-300">
                                @php
                                    $st = $selected_log->subject_type ? class_basename($selected_log->subject_type) : null;
                                    $sid = $selected_log->subject_id ? ('#' . $selected_log->subject_id) : null;
                                @endphp
                                <div class="text-sm text-slate-900 font-medium">
                                    {{ $st ? ($st . ' ' . ($sid ?? '')) : '-' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 mb-6">
                        <div class="flex items-center gap-2 mb-2">
                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <label class="text-sm font-medium text-slate-700">{{ __('Description') }}</label>
                        </div>
                        <div class="p-3 bg-white rounded-lg border border-slate-300">
                            <div class="text-sm text-slate-900">
                                {{ $selected_log->description ?? '-' }}
                            </div>
                        </div>
                    </div>

                    <!-- Technical Details -->
                    <div class="bg-amber-50 rounded-xl p-4 border border-amber-200">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <label class="text-sm font-medium text-amber-700">{{ __('Technical Details') }}</label>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-amber-600 mb-1">{{ __('IP Address') }}</div>
                                <div class="p-2 bg-white rounded border border-amber-300 text-sm font-mono">
                                    {{ $selected_log->ip_address ?? '-' }}
                                </div>
                            </div>
                            <div>
                                <div class="text-xs text-amber-600 mb-1">{{ __('Log ID') }}</div>
                                <div class="p-2 bg-white rounded border border-amber-300 text-sm font-mono">
                                    #{{ $selected_log->id }}
                                </div>
                            </div>
                        </div>
                        
                        @if ($selected_log->meta && is_array($selected_log->meta) && count($selected_log->meta) > 0)
                            <div class="mt-4">
                                <div class="text-xs text-amber-600 mb-2">{{ __('Additional Details') }}</div>
                                <div class="p-3 bg-white rounded border border-amber-300">
                                    @php
                                        // Define readable labels for common fields
                                        $fieldLabels = [
                                            'name' => __('Product Name'),
                                            'branch_id' => __('Branch ID'),
                                            'unit_type_id' => __('Unit Type'),
                                            'selling_price' => __('Selling Price'),
                                            'cost_price' => __('Cost Price'),
                                            'min_selling_price' => __('Min Selling Price'),
                                            'bulk_enabled' => __('Bulk Enabled'),
                                            'bulk_type_id' => __('Bulk Type'),
                                            'status' => __('Status'),
                                            'opening_quantity' => __('Opening Quantity'),
                                            'opening_cost_price' => __('Opening Cost Price'),
                                            'opening_expiry_date' => __('Opening Expiry Date'),
                                            'product_date' => __('Product Date'),
                                            'received_at_date' => __('Received Date'),
                                            'sold_at_date' => __('Sale Date'),
                                            'quantity' => __('Quantity'),
                                            'is_active' => __('Active Status'),
                                            'permissions' => __('Permissions'),
                                            'roles' => __('Roles'),
                                            'discount_pct' => __('Discount %'),
                                            'new_price' => __('New Price'),
                                            'days_min' => __('Days Min'),
                                            'days_max' => __('Days Max'),
                                            'organization' => __('Organization'),
                                            'receipt_number' => __('Receipt Number'),
                                            'reason' => __('Reason'),
                                            'method' => __('Method'),
                                            'removed_roles' => __('Removed Roles'),
                                            'old_status' => __('Old Status'),
                                            'new_status' => __('New Status'),
                                            'branch_id' => __('Branch ID')
                                        ];
                                    @endphp
                                    
                                    <div class="space-y-2">
                                        @foreach ($selected_log->meta as $key => $value)
                                            <div class="flex items-start gap-2">
                                                <div class="text-xs font-medium text-amber-700 min-w-24">
                                                    {{ $fieldLabels[$key] ?? ucfirst(str_replace('_', ' ', $key)) }}:
                                                </div>
                                                <div class="text-xs text-slate-700 flex-1">
                                                    @if (is_bool($value))
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs {{ $value ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                            {{ $value ? __('Yes') : __('No') }}
                                                        </span>
                                                    @elseif (is_array($value))
                                                        <div class="bg-slate-50 rounded p-2">
                                                            @if (empty($value))
                                                                <span class="text-slate-500 italic">{{ __('None') }}</span>
                                                            @else
                                                                @foreach ($value as $item)
                                                                    <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded mr-1 mb-1">
                                                                        {{ is_string($item) ? $item : json_encode($item) }}
                                                                    </span>
                                                                @endforeach
                                                            @endif
                                                        </div>
                                                    @elseif ($key === 'selling_price' || $key === 'cost_price' || $key === 'min_selling_price' || $key === 'new_price' || $key === 'opening_cost_price')
                                                        <span class="font-mono bg-green-50 text-green-800 px-2 py-1 rounded">
                                                            XAF {{ number_format((float) $value, 2) }}
                                                        </span>
                                                    @elseif ($key === 'opening_expiry_date' || $key === 'product_date' || $key === 'received_at_date' || $key === 'sold_at_date')
                                                        <span class="font-mono bg-blue-50 text-blue-800 px-2 py-1 rounded">
                                                            {{ $value ? \Carbon\Carbon::parse($value)->format('M j, Y') : '-' }}
                                                        </span>
                                                    @elseif ($key === 'status' || $key === 'old_status' || $key === 'new_status')
                                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs {{ $value === 'active' ? 'bg-green-100 text-green-800' : ($value === 'inactive' ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-800') }}">
                                                            {{ ucfirst($value) }}
                                                        </span>
                                                    @elseif (is_null($value))
                                                        <span class="text-slate-500 italic">{{ __('Not set') }}</span>
                                                    @else
                                                        <span class="font-mono bg-slate-50 text-slate-800 px-2 py-1 rounded">
                                                            {{ is_string($value) ? $value : json_encode($value) }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
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
