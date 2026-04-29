<div class="ui-page">
    <div class="ui-page-container">
        {{-- TODAY'S KEY METRICS --}}
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __("Today's Overview") }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Total Sales --}}
                <div class="ui-kpi-card">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="ui-kpi-title">{{ __('Total Sales') }}</div>
                            <div class="ui-kpi-value">XAF {{ number_format($this->today_stats['sales'], 0, ',', ' ') }}</div>
                        </div>
                        <div class="flex items-center gap-1 {{ $this->today_stats['sales_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            @if ($this->today_stats['sales_change'] >= 0)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            @endif
                            <span class="text-sm font-medium">{{ abs($this->today_stats['sales_change']) }}%</span>
                        </div>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('vs yesterday') }}</div>
                </div>

                {{-- Net Profit --}}
                <div class="ui-kpi-card">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="ui-kpi-title">{{ __('Net Profit') }}</div>
                            <div class="ui-kpi-value">XAF {{ number_format($this->today_stats['profit'], 0, ',', ' ') }}</div>
                        </div>
                        <div class="flex items-center gap-1 {{ $this->today_stats['profit_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            @if ($this->today_stats['profit_change'] >= 0)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            @endif
                            <span class="text-sm font-medium">{{ abs($this->today_stats['profit_change']) }}%</span>
                        </div>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('vs yesterday') }}</div>
                </div>

                {{-- Transactions --}}
                <div class="ui-kpi-card">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="ui-kpi-title">{{ __('Transactions') }}</div>
                            <div class="ui-kpi-value">{{ $this->today_stats['transactions'] }}</div>
                        </div>
                        <div class="flex items-center gap-1 {{ $this->today_stats['transactions_change'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            @if ($this->today_stats['transactions_change'] >= 0)
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            @endif
                            <span class="text-sm font-medium">{{ abs($this->today_stats['transactions_change']) }}%</span>
                        </div>
                    </div>
                    <div class="text-xs text-slate-500 mt-1">{{ __('vs yesterday') }}</div>
                </div>
            </div>
        </div>

        {{-- STOCK STATS --}}
        <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-blue-600 font-medium">{{ __('Stock Value') }}</div>
                        <div class="text-lg font-bold text-blue-900">XAF {{ number_format($this->stock_stats['value'], 0, ',', ' ') }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-purple-600 font-medium">{{ __('Stock Items') }}</div>
                        <div class="text-lg font-bold text-purple-900">{{ $this->stock_stats['items'] }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-4 border border-amber-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-amber-600 font-medium">{{ __('Low Stock Items') }}</div>
                        <div class="text-lg font-bold text-amber-900">{{ $this->stock_stats['low_stock_count'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- QUICK ACTIONS --}}
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4">{{ __('Quick Actions') }}</h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <a href="{{ route('sales.add') }}" class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-blue hover:shadow-md transition-all group">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-200 transition-colors">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700">{{ __('New Sale') }}</span>
                </a>

                <a href="{{ route('stock_in.index') }}" class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-blue hover:shadow-md transition-all group">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-10 11h6" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700">{{ __('Add Stock') }}</span>
                </a>

                <a href="{{ route('products.index') }}" class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-blue hover:shadow-md transition-all group">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700">{{ __('New Product') }}</span>
                </a>

                <a href="{{ route('reports.index') }}" class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-blue hover:shadow-md transition-all group">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center group-hover:bg-amber-200 transition-colors">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700">{{ __('View Reports') }}</span>
                </a>
            </div>
        </div>

        {{-- ALERTS & NOTIFICATIONS --}}
        <div class="mb-6 grid grid-cols-1 {{ $this->hasClearancePermission ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }} gap-6">
            @if ($this->hasClearancePermission)
                {{-- Clearance Alerts --}}
                <div class="ui-card bg-orange-50 border-orange-200">
                    <div class="ui-card-body">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-orange-900">{{ __('Clearance Items') }}</h3>
                        </div>
                        @if ($this->clearanceCount > 0)
                            <div class="text-center py-2">
                                <div class="text-2xl font-bold text-orange-700">{{ $this->clearanceCount }}</div>
                                <div class="text-xs text-orange-600">{{ __('items need action') }}</div>
                            </div>
                            <a href="{{ route('clearance.index') }}" class="mt-2 block text-center text-xs font-medium text-orange-700 hover:text-orange-800">
                                {{ __('View Clearance Manager') }} →
                            </a>
                        @else
                            <div class="text-sm text-orange-600 text-center py-2">{{ __('No clearance items pending') }}</div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Low Stock Alerts --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-900">{{ __('Low Stock Items') }}</h3>
                    </div>
                    @if ($this->low_stock_items->count() > 0)
                        <div class="space-y-2">
                            @foreach ($this->low_stock_items as $stock)
                                <div class="flex items-center justify-between p-2 bg-red-50 rounded-lg">
                                    <span class="text-sm text-slate-700">{{ $stock->product?->name ?? '-' }}</span>
                                    <span class="text-xs font-medium text-red-600">{{ $stock->current_stock }} left</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-slate-500 text-center py-4">{{ __('All items are well stocked') }}</div>
                    @endif
                </div>
            </div>

            {{-- Expiring Products --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-900">{{ __('Expiring Soon') }}</h3>
                    </div>
                    @if ($this->expiring_products->count() > 0)
                        <div class="space-y-2">
                            @foreach ($this->expiring_products as $stock)
                                <div class="flex items-center justify-between p-2 bg-amber-50 rounded-lg">
                                    <span class="text-sm text-slate-700">{{ $stock->product_name }}</span>
                                    <span class="text-xs font-medium text-amber-600">{{ Carbon\Carbon::parse($stock->expiry_date)->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-slate-500 text-center py-4">{{ __('No items expiring soon') }}</div>
                    @endif
                </div>
            </div>

            {{-- Recent Activity --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-900">{{ __('Recent Activity') }}</h3>
                    </div>
                    @if ($this->recent_activity->count() > 0)
                        <div class="space-y-2">
                            @foreach ($this->recent_activity as $activity)
                                <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg">
                                    <div>
                                        <div class="text-sm text-slate-700">{{ $activity['description'] }}</div>
                                        <div class="text-xs text-slate-500">{{ $activity['user'] }}</div>
                                    </div>
                                    @if ($activity['amount'])
                                        <span class="text-xs font-medium text-green-600">XAF {{ number_format($activity['amount'], 0, ',', ' ') }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-sm text-slate-500 text-center py-4">{{ __('No recent activity') }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- SALES TREND & TOP PRODUCTS --}}
        <div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Sales Trend Chart --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Sales Trend (7 Days)') }}</h3>
                    <div class="mt-4">
                        <div class="flex items-end justify-between h-40 gap-2">
                            @foreach ($this->sales_trend['labels'] as $i => $label)
                                @php
                                    $value = $this->sales_trend['data'][$i];
                                    $max = max($this->sales_trend['data']) ?: 1;
                                    $height = $max > 0 ? ($value / $max) * 100 : 0;
                                @endphp
                                <div class="flex-1 flex flex-col items-center gap-1">
                                    <div class="text-xs text-slate-500">XAF {{ number_format($value, 0, ',', ' ') }}</div>
                                    <div class="w-full bg-primary-blue/20 rounded-t relative" style="height: {{ max($height, 4) }}%">
                                        <div class="absolute inset-0 bg-primary-blue rounded-t opacity-80 hover:opacity-100 transition-opacity"></div>
                                    </div>
                                    <div class="text-xs text-slate-500">{{ $label }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- Top Performing Products --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Top Products This Week') }}</h3>
                    <div class="mt-4">
                        @if ($this->top_products->count() > 0)
                            <div class="space-y-3">
                                @foreach ($this->top_products as $i => $product)
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded-full bg-primary-blue text-white text-xs flex items-center justify-center font-medium">
                                            {{ $i + 1 }}
                                        </div>
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-slate-900">{{ $product->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $product->qty_sold }} sold</div>
                                        </div>
                                        <div class="text-sm font-semibold text-green-600">XAF {{ number_format($product->revenue, 0, ',', ' ') }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-sm text-slate-500 text-center py-8">{{ __('No sales data for this week') }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- PROFIT SUMMARY & RECENT SALES --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Profit Summary --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Profit Summary (This Month)') }}</h3>
                    <div class="mt-4 space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-sm text-slate-600">{{ __('Gross Revenue') }}</span>
                            <span class="text-sm font-semibold text-slate-900">XAF {{ number_format($this->profit_summary['gross_revenue'], 0, ',', ' ') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-sm text-slate-600">{{ __('Cost of Goods Sold') }}</span>
                            <span class="text-sm font-semibold text-red-600">-XAF {{ number_format($this->profit_summary['cogs'], 0, ',', ' ') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-sm text-slate-600">{{ __('Gross Profit') }}</span>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-green-600">XAF {{ number_format($this->profit_summary['gross_profit'], 0, ',', ' ') }}</div>
                                <div class="text-xs text-slate-500">{{ $this->profit_summary['gross_margin'] }}% margin</div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-sm text-slate-600">{{ __('Operating Expenses') }}</span>
                            <span class="text-sm font-semibold text-red-600">-XAF {{ number_format($this->profit_summary['expenses'], 0, ',', ' ') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 bg-green-50 rounded-lg px-3 -mx-3">
                            <span class="text-sm font-semibold text-slate-900">{{ __('Net Profit') }}</span>
                            <div class="text-right">
                                <div class="text-lg font-bold text-green-600">XAF {{ number_format($this->profit_summary['net_profit'], 0, ',', ' ') }}</div>
                                <div class="text-xs text-slate-500">{{ $this->profit_summary['net_margin'] }}% margin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Sales --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="ui-card-title mb-0">{{ __('Recent Sales') }}</h3>
                        <a href="{{ route('sales.add') }}" class="text-sm text-primary-blue hover:underline">{{ __('View All') }}</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Receipt') }}</th>
                                    <th>{{ __('Time') }}</th>
                                    <th>{{ __('Amount') }}</th>
                                    <th>{{ __('Staff') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($this->recent_sales as $sale)
                                    <tr>
                                        <td class="font-medium text-slate-900">{{ $sale->receipt_no }}</td>
                                        <td class="text-sm text-slate-600">{{ $sale->sold_at->format('H:i') }}</td>
                                        <td class="font-semibold text-green-600">XAF {{ number_format($sale->grand_total, 0, ',', ' ') }}</td>
                                        <td class="text-sm text-slate-600">{{ $sale->user?->name ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-sm text-slate-500 py-4">{{ __('No recent sales') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

@script
<script>
    function renderDashboardCharts() {
        const el = document.getElementById('salesTrendDashboardChart');
        if (!el || !window.Chart) return;

        const labels = @json($this->sales_trend['labels']);
        const data = @json($this->sales_trend['data']);

        if (el._chart) { el._chart.destroy(); }

        el._chart = new Chart(el, {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Sales',
                    data,
                    backgroundColor: 'rgba(37, 99, 235, 0.25)',
                    borderColor: '#2563EB',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Sales: XAF ' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: { display: false },
                    y: { display: false, beginAtZero: true }
                }
            }
        });
    }

    function initDashboardCharts() {
        if (typeof window.Chart === 'undefined') {
            setTimeout(initDashboardCharts, 100);
            return;
        }
        renderDashboardCharts();
    }
    
    initDashboardCharts();
    Livewire.hook('morph.updated', () => { setTimeout(initDashboardCharts, 100); });
    document.addEventListener('livewire:navigated', () => { setTimeout(initDashboardCharts, 100); });
</script>
@endscript
