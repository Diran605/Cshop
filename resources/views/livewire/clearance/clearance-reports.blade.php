<div class="ui-page">
    <div class="ui-page-container">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('Clearance Performance Report') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ __('Track clearance sales, recovery rates, and losses') }}</p>
            </div>
            <div class="flex items-center gap-3">
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
                <a href="{{ route('clearance.index') }}" class="ui-btn-secondary">
                    {{ __('Back to Manager') }}
                </a>
            </div>
        </div>

        {{-- Date Filters --}}
        <div class="ui-card mb-6">
            <div class="ui-card-body">
                <div class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('From') }}</label>
                        <input type="date" wire:model.live="date_from" class="ui-input">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('To') }}</label>
                        <input type="date" wire:model.live="date_to" class="ui-input">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Action Type') }}</label>
                        <select wire:model.live="filter_action" class="ui-input">
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
                <div class="text-xs text-slate-500">{{ __('Items Processed') }}</div>
                <div class="text-2xl font-bold text-slate-900">{{ $this->stats['total_items'] }}</div>
            </div>

            <div class="ui-kpi-card">
                <div class="text-xs text-slate-500">{{ __('Original Value') }}</div>
                <div class="text-xl font-bold text-slate-900">XAF {{ number_format($this->stats['total_original_value'], 0, ',', ' ') }}</div>
            </div>

            <div class="ui-kpi-card bg-green-50 border-green-200">
                <div class="text-xs text-green-600">{{ __('Recovered Value') }}</div>
                <div class="text-xl font-bold text-green-700">XAF {{ number_format($this->stats['total_recovered_value'], 0, ',', ' ') }}</div>
            </div>

            <div class="ui-kpi-card bg-red-50 border-red-200">
                <div class="text-xs text-red-600">{{ __('Loss Value') }}</div>
                <div class="text-xl font-bold text-red-700">XAF {{ number_format($this->stats['total_loss_value'], 0, ',', ' ') }}</div>
            </div>

            <div class="ui-kpi-card">
                <div class="text-xs text-slate-500">{{ __('Recovery Rate') }}</div>
                <div class="text-2xl font-bold {{ $this->stats['recovery_rate'] >= 50 ? 'text-green-600' : 'text-amber-600' }}">{{ $this->stats['recovery_rate'] }}%</div>
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

            {{-- Daily Trend Chart --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Daily Trend') }}</h3>
                    @if ($this->dailyTrend->count() > 0)
                        <div class="mt-4">
                            <div class="flex items-end justify-between h-40 gap-1">
                                @foreach ($this->dailyTrend as $day)
                                    @php
                                        $max = $this->dailyTrend->max('recovered') ?: 1;
                                        $height = $max > 0 ? ($day->recovered / $max) * 100 : 0;
                                    @endphp
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="text-[10px] text-slate-400">XAF {{ number_format($day->recovered, 0, ',', ' ') }}</div>
                                        <div class="w-full bg-green-200 rounded-t relative" style="height: {{ max($height, 4) }}%">
                                            <div class="absolute inset-0 bg-green-500 rounded-t opacity-80"></div>
                                        </div>
                                        <div class="text-[10px] text-slate-400">{{ Carbon\Carbon::parse($day->date)->format('d') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="mt-4 text-center py-8 text-slate-500">{{ __('No data for selected period') }}</div>
                    @endif
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
