<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Daily Sales Summary') }}</h2>
            <div class="ui-page-subtitle">{{ __('Overview of sales performance and statistics.') }}</div>
        </div>

        <!-- Date Selector -->
        <div class="ui-card mb-6">
            <div class="ui-card-body">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="previousDay" class="ui-btn-secondary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </button>
                        <div class="flex items-center gap-2">
                            <input type="date" wire:model.live="summary_date" class="ui-input" />
                            <button type="button" wire:click="today" class="ui-btn-secondary">
                                {{ __('Today') }}
                            </button>
                        </div>
                        <button type="button" wire:click="nextDay" class="ui-btn-secondary">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    </div>

                    @if ($isSuperAdmin)
                        <div>
                            <select wire:model.live="branch_id" class="ui-select">
                                <option value="0">{{ __('All Branches') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="mt-4 text-center">
                    <div class="text-2xl font-semibold text-slate-900">{{ $date->format('l, F j, Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-6">
            <div class="ui-card">
                <div class="ui-card-body text-center">
                    <div class="text-sm text-slate-500">{{ __('Total Sales') }}</div>
                    <div class="mt-2 text-3xl font-bold text-blue-600">{{ $totalSales }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body text-center">
                    <div class="text-sm text-slate-500">{{ __('Voided') }}</div>
                    <div class="mt-2 text-3xl font-bold text-red-600">{{ $voidedSales }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body text-center">
                    <div class="text-sm text-slate-500">{{ __('Revenue') }}</div>
                    <div class="mt-2 text-xl font-bold text-green-600">XAF {{ number_format((float) $totalRevenue, 2) }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body text-center">
                    <div class="text-sm text-slate-500">{{ __('Cost') }}</div>
                    <div class="mt-2 text-xl font-bold text-orange-600">XAF {{ number_format((float) $totalCost, 2) }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body text-center">
                    <div class="text-sm text-slate-500">{{ __('Profit') }}</div>
                    <div class="mt-2 text-xl font-bold {{ $totalProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">XAF {{ number_format((float) $totalProfit, 2) }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body text-center">
                    <div class="text-sm text-slate-500">{{ __('Profit Margin') }}</div>
                    <div class="mt-2 text-xl font-bold {{ $totalRevenue > 0 && ($totalProfit / $totalRevenue * 100) >= 10 ? 'text-emerald-600' : 'text-orange-600' }}">
                        @if ($totalRevenue > 0)
                            {{ number_format($totalProfit / $totalRevenue * 100, 1) }}%
                        @else
                            0%
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Payment Methods -->
            <div class="ui-card">
                <div class="ui-card-header">
                    <h3 class="ui-card-title">{{ __('Sales by Payment Method') }}</h3>
                </div>
                <div class="ui-card-body">
                    @if ($salesByPayment->count() > 0)
                        <div class="space-y-3">
                            @foreach ($salesByPayment as $method => $data)
                                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $method === 'cash' ? 'bg-green-100 text-green-800' : ($method === 'mobile' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800') }}">
                                            {{ ucfirst($method) }}
                                        </span>
                                        <span class="text-sm text-slate-600">{{ $data->count }} sales</span>
                                    </div>
                                    <div class="font-mono font-semibold">XAF {{ number_format((float) $data->total, 2) }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-slate-500">
                            {{ __('No sales data for this day.') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Hourly Breakdown -->
            <div class="ui-card">
                <div class="ui-card-header">
                    <h3 class="ui-card-title">{{ __('Hourly Breakdown') }}</h3>
                </div>
                <div class="ui-card-body">
                    @if ($hourlySales->count() > 0)
                        <div class="space-y-2">
                            @php
                                $maxTotal = $hourlySales->max('total') ?? 1;
                            @endphp
                            @foreach ($hourlySales as $hour => $data)
                                <div class="flex items-center gap-3">
                                    <div class="w-16 text-sm text-slate-600 font-mono">
                                        {{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00
                                    </div>
                                    <div class="flex-1">
                                        <div class="h-6 bg-slate-100 rounded-full overflow-hidden">
                                            <div class="h-full bg-blue-500 rounded-full" style="width: {{ ($data->total / $maxTotal) * 100 }}%"></div>
                                        </div>
                                    </div>
                                    <div class="w-24 text-right">
                                        <span class="text-sm font-mono">{{ $data->count }}</span>
                                    </div>
                                    <div class="w-32 text-right font-mono text-sm">
                                        XAF {{ number_format((float) $data->total, 0) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-slate-500">
                            {{ __('No hourly data for this day.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Top Selling Products -->
            <div class="ui-card">
                <div class="ui-card-header">
                    <h3 class="ui-card-title">{{ __('Top Selling Products') }}</h3>
                </div>
                <div class="ui-card-body">
                    @if ($topProducts->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="ui-table text-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th class="text-right">{{ __('Qty') }}</th>
                                        <th class="text-right">{{ __('Revenue') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topProducts as $item)
                                        <tr>
                                            <td>{{ $item->product?->name ?? '-' }}</td>
                                            <td class="text-right font-mono">{{ (int) $item->total_qty }}</td>
                                            <td class="text-right font-mono">XAF {{ number_format((float) $item->total_revenue, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-slate-500">
                            {{ __('No products sold on this day.') }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Sales -->
            <div class="ui-card">
                <div class="ui-card-header">
                    <h3 class="ui-card-title">{{ __('Recent Sales') }}</h3>
                </div>
                <div class="ui-card-body">
                    @if ($recentSales->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="ui-table text-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Receipt') }}</th>
                                        @if ($isSuperAdmin && $branch_id === 0)
                                            <th>{{ __('Branch') }}</th>
                                        @endif
                                        <th>{{ __('Time') }}</th>
                                        <th class="text-right">{{ __('Total') }}</th>
                                        <th>{{ __('Status') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentSales as $sale)
                                        <tr class="{{ $sale->voided_at ? 'bg-red-50 opacity-60' : '' }}">
                                            <td class="font-mono">{{ $sale->receipt_no }}</td>
                                            @if ($isSuperAdmin && $branch_id === 0)
                                                <td>{{ $sale->branch?->name ?? '-' }}</td>
                                            @endif
                                            <td>{{ $sale->sold_at?->format('H:i') ?? '-' }}</td>
                                            <td class="text-right font-mono">XAF {{ number_format((float) $sale->grand_total, 2) }}</td>
                                            <td>
                                                @if ($sale->voided_at)
                                                    <span class="ui-badge-warning">{{ __('Voided') }}</span>
                                                @else
                                                    <span class="ui-badge-success">{{ __('Active') }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-slate-500">
                            {{ __('No sales on this day.') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Branch Comparison (Super Admin Only) -->
        @if ($isSuperAdmin && $branch_id === 0 && $branchComparison->count() > 0)
            <div class="ui-card">
                <div class="ui-card-header">
                    <h3 class="ui-card-title">{{ __('Branch Comparison') }}</h3>
                </div>
                <div class="ui-card-body">
                    <div class="overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Branch') }}</th>
                                    <th class="text-right">{{ __('Sales') }}</th>
                                    <th class="text-right">{{ __('Revenue') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($branchComparison as $branch)
                                    <tr>
                                        <td class="font-medium">{{ $branch->name }}</td>
                                        <td class="text-right">{{ (int) $branch->total_sales }}</td>
                                        <td class="text-right font-mono">XAF {{ number_format((float) ($branch->total_revenue ?? 0), 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
