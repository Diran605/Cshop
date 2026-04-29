<div class="ui-page">
    <div class="ui-page-container print-container">
        {{-- Header --}}
        <div class="mb-6">
            <h2 class="ui-page-title text-2xl font-bold text-slate-900">{{ __('Clearance Performance Report') }}</h2>
            <p class="ui-page-subtitle text-slate-500">{{ __('Track clearance sales, recovery rates, and losses') }}</p>
        </div>

        <div class="ui-card no-print mb-6">
            <div class="ui-card-body">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                    <div class="ui-tabs">
                        <a href="{{ route('reports.index') }}" class="ui-tab">{{ __('Sales') }}</a>
                        <a href="{{ route('reports.profit') }}" class="ui-tab">{{ __('Profit') }}</a>
                        <a href="{{ route('reports.stock') }}" class="ui-tab">{{ __('Stock') }}</a>
                        <a href="{{ route('reports.expenses') }}" class="ui-tab">{{ __('Expenses') }}</a>
                        <a href="{{ route('reports.expiry') }}" class="ui-tab">{{ __('Expiry') }}</a>
                        <a href="{{ route('clearance.reports') }}" class="ui-tab ui-tab-active">{{ __('Clearance') }}</a>
                        <a href="{{ route('daily_summary.index') }}" class="ui-tab">{{ __('Summary') }}</a>
                        <a href="{{ route('stock_valuation.index') }}" class="ui-tab">{{ __('Valuation') }}</a>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="window.print()" class="ui-btn-primary">
                            {{ __('Print') }}
                        </button>
                        <a href="{{ route('clearance.index') }}" class="ui-btn-secondary">
                            {{ __('Back to Manager') }}
                        </a>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="ui-label text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1 block">{{ __('Period') }}</label>
                        <div class="flex gap-2">
                            <input type="date" wire:model.live="date_from" class="ui-input flex-1">
                            <input type="date" wire:model.live="date_to" class="ui-input flex-1">
                        </div>
                    </div>
                    <div>
                        <label class="ui-label text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1 block">{{ __('Branch') }}</label>
                        @if ($this->isSuperAdmin)
                            <select wire:model.live="filter_branch_id" class="ui-select w-full">
                                <option value="0">{{ __('All Branches') }}</option>
                                @foreach ($this->branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <div class="ui-input bg-slate-50 text-slate-500">{{ $this->branches->firstWhere('id', $filter_branch_id)?->name ?? '-' }}</div>
                        @endif
                    </div>
                    <div>
                        <label class="ui-label text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1 block">{{ __('Action Type') }}</label>
                        <select wire:model.live="filter_action" class="ui-select w-full">
                            <option value="all">{{ __('All Actions') }}</option>
                            <option value="sold">{{ __('Sold') }}</option>
                            <option value="discount">{{ __('Discounted') }}</option>
                            <option value="donate">{{ __('Donated') }}</option>
                            <option value="dispose">{{ __('Disposed') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPI Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="ui-kpi-card">
                <div class="ui-kpi-title">{{ __('Items Processed') }}</div>
                <div class="ui-kpi-value">{{ $this->stats['total_items'] }}</div>
            </div>

            <div class="ui-kpi-card">
                <div class="ui-kpi-title">{{ __('Original Value') }}</div>
                <div class="ui-kpi-value">XAF {{ number_format($this->stats['total_original_value'], 0, ',', ' ') }}</div>
            </div>

            <div class="ui-kpi-card">
                <div class="ui-kpi-title text-emerald-600">{{ __('Recovered Value') }}</div>
                <div class="ui-kpi-value text-emerald-600">XAF {{ number_format($this->stats['total_recovered_value'], 0, ',', ' ') }}</div>
            </div>

            <div class="ui-kpi-card">
                <div class="ui-kpi-title text-rose-600">{{ __('Loss Value') }}</div>
                <div class="ui-kpi-value text-rose-600">XAF {{ number_format($this->stats['total_loss_value'], 0, ',', ' ') }}</div>
            </div>

            <div class="ui-kpi-card">
                <div class="ui-kpi-title">{{ __('Recovery Rate') }}</div>
                <div class="ui-kpi-value {{ $this->stats['recovery_rate'] >= 50 ? 'text-emerald-600' : 'text-amber-600' }}">{{ $this->stats['recovery_rate'] }}%</div>
            </div>
        </div>

        {{-- Breakdown by Action Type --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Breakdown by Action') }}</h3>
                    <div class="mt-4 space-y-3">
                        @foreach (['sold' => __('Sold'), 'discount' => __('Discounted'), 'donate' => __('Donated'), 'dispose' => __('Disposed')] as $type => $label)
                            @php
                                $data = $this->stats['by_type']->get($type);
                            @endphp
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center {{ $type === 'sold' ? 'bg-green-100 text-green-600' : ($type === 'discount' ? 'bg-blue-100 text-blue-600' : ($type === 'donate' ? 'bg-purple-100 text-purple-600' : 'bg-red-100 text-red-600')) }}">
                                        @if ($type === 'sold')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @elseif ($type === 'discount')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                                        @elseif ($type === 'donate')
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-900">{{ $label }}</div>
                                        <div class="text-xs text-slate-500">{{ $data?->qty ?? 0 }} {{ __('items') }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold {{ $type === 'dispose' || $type === 'donate' ? 'text-red-600' : 'text-green-600' }}">
                                        @if ($type === 'dispose' || $type === 'donate')
                                            -XAF {{ number_format($data?->loss ?? 0, 0, ',', ' ') }}
                                        @else
                                            XAF {{ number_format($data?->recovered ?? 0, 0, ',', ' ') }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Actions --}}
        <div class="ui-card">
            <div class="ui-card-body p-0">
                <div class="p-4 border-b border-slate-200">
                    <h3 class="ui-card-title">{{ __('Recent Clearance Actions') }}</h3>
                </div>
                @if ($this->recentActions->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Date') }}</th>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Action') }}</th>
                                    <th class="text-right">{{ __('Qty') }}</th>
                                    <th class="text-right">{{ __('Original') }}</th>
                                    <th class="text-right">{{ __('Recovered') }}</th>
                                    <th class="text-right">{{ __('Loss') }}</th>
                                    <th>{{ __('By') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->recentActions as $action)
                                    <tr>
                                        <td>{{ $action->created_at->format('d M H:i') }}</td>
                                        <td>{{ $action->clearanceItem?->product?->name ?? '-' }}</td>
                                        <td>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $action->action_type === 'sold' ? 'bg-green-100 text-green-800' : ($action->action_type === 'discount' ? 'bg-blue-100 text-blue-800' : ($action->action_type === 'donate' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800')) }}">
                                                {{ __($action->action_type === 'sold' ? 'Sold' : ($action->action_type === 'discount' ? 'Discounted' : ($action->action_type === 'donate' ? 'Donated' : 'Disposed'))) }}
                                            </span>
                                        </td>
                                        <td class="text-right">{{ $action->quantity }}</td>
                                        <td class="text-right">XAF {{ number_format($action->original_value, 0, ',', ' ') }}</td>
                                        <td class="text-right text-green-600">XAF {{ number_format($action->recovered_value, 0, ',', ' ') }}</td>
                                        <td class="text-right text-red-600">XAF {{ number_format($action->loss_value, 0, ',', ' ') }}</td>
                                        <td class="text-sm text-slate-500">{{ $action->user?->name ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-12 text-center text-slate-500">{{ __('No actions recorded for this period') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
