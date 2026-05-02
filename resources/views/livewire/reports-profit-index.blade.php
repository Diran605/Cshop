<div class="ui-page">
    <div class="ui-page-container print-container">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="ui-page-title">{{ __('Profit Report') }}</h1>
                <p class="ui-page-subtitle">{{ __('Monitor profitability and margin performance.') }}</p>
            </div>
            <div class="flex items-center gap-3 no-print">
                <div class="ui-tabs">
                    <a href="{{ route('reports.index') }}" class="ui-tab">{{ __('Sales') }}</a>
                    <a href="{{ route('reports.profit') }}" class="ui-tab ui-tab-active">{{ __('Profit') }}</a>
                    <a href="{{ route('reports.stock') }}" class="ui-tab">{{ __('Stock') }}</a>
                    <a href="{{ route('reports.expenses') }}" class="ui-tab">{{ __('Expenses') }}</a>
                    <a href="{{ route('reports.expiry') }}" class="ui-tab">{{ __('Expiry') }}</a>
                    <a href="{{ route('clearance.reports') }}" class="ui-tab">{{ __('Clearance') }}</a>
                    <a href="{{ route('daily_summary.index') }}" class="ui-tab">{{ __('Summary') }}</a>
                    <a href="{{ route('stock_valuation.index') }}" class="ui-tab">{{ __('Valuation') }}</a>
                </div>
                <button onclick="window.print()" class="ui-btn-primary gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    {{ __('Print') }}
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="ui-card no-print mb-8">
            <div class="ui-card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Date Range -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="ui-label text-xs uppercase tracking-wider text-slate-500 mb-1">{{ __('From') }}</label>
                            <input type="date" wire:model.live="date_from" class="ui-input">
                        </div>
                        <div>
                            <label class="ui-label text-xs uppercase tracking-wider text-slate-500 mb-1">{{ __('To') }}</label>
                            <input type="date" wire:model.live="date_to" class="ui-input">
                        </div>
                    </div>

                    <!-- Branch Selection -->
                    <div>
                        <label class="ui-label text-xs uppercase tracking-wider text-slate-500 mb-1">{{ __('Branch') }}</label>
                        @if ($isSuperAdmin)
                            <select wire:model.live="branch_id" class="ui-select">
                                <option value="0">{{ __('All Branches') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <div class="ui-input bg-slate-50 text-slate-500">
                                {{ $branches->first()?->name ?? __('My Branch') }}
                            </div>
                        @endif
                    </div>

                    <!-- Category Selection -->
                    <div>
                        <label class="ui-label text-xs uppercase tracking-wider text-slate-500 mb-1">{{ __('Category') }}</label>
                        <select wire:model.live="category_id" class="ui-select">
                            <option value="0">{{ __('All Categories') }}</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            @php
                $metrics = [
                    ['label' => __('Gross Profit'), 'value' => number_format($grossProfit, 2), 'change' => $grossProfitChange, 'prefix' => 'XAF ', 'color' => 'emerald'],
                    ['label' => __('Net Profit'), 'value' => number_format($netProfit, 2), 'change' => $netProfitChange, 'prefix' => 'XAF ', 'color' => 'blue'],
                    ['label' => __('Gross Margin'), 'value' => number_format($grossMargin, 1), 'change' => $marginChange, 'suffix' => '%', 'color' => 'indigo'],
                    ['label' => __('Expenses'), 'value' => number_format($expenseTotal, 2), 'change' => $expenseChange, 'prefix' => 'XAF ', 'color' => 'rose', 'inverse' => true],
                ];
            @endphp
            @foreach($metrics as $metric)
            <div class="ui-kpi-card">
                <div class="ui-kpi-title">{{ $metric['label'] }}</div>
                <div class="mt-2 flex items-baseline justify-between">
                    <div class="ui-kpi-value">
                        {{ ($metric['prefix'] ?? '') . $metric['value'] . ($metric['suffix'] ?? '') }}
                    </div>
                    <div class="inline-flex items-center text-sm font-semibold {{ ($metric['inverse'] ?? false) ? ($metric['change'] <= 0 ? 'text-emerald-600' : 'text-rose-600') : ($metric['change'] >= 0 ? 'text-emerald-600' : 'text-rose-600') }}">
                        @if($metric['change'] >= 0)
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                        @else
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        @endif
                        {{ abs(round($metric['change'], 1)) }}{{ isset($metric['suffix']) && $metric['label'] === __('Gross Margin') ? 'pp' : '%' }}
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Profit Trend -->
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="text-lg font-bold text-slate-900 mb-6">{{ __('Profit Trend') }}</h3>
                    <div class="h-80" wire:ignore>
                        <canvas id="profitTrendChart" wire:key="profit-trend-canvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Category Profitability -->
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="text-lg font-bold text-slate-900 mb-6">{{ __('Category Profitability') }}</h3>
                    <div class="h-80" wire:ignore>
                        <canvas id="categoryProfitChart" wire:key="category-profit-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tables Section -->
        <div class="ui-card">
            <div class="ui-card-body p-0">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Top Products by Profit') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="ui-table">
                        <thead>
                            <tr>
                                <th>{{ __('Product') }}</th>
                                <th class="text-right">{{ __('Qty Sold') }}</th>
                                <th class="text-right">{{ __('Profit') }}</th>
                                <th class="text-right">{{ __('Margin') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProductsByProfit as $product)
                            <tr>
                                <td>
                                    <div class="font-medium text-slate-900">{{ $product->product_name }}</div>
                                </td>
                                <td class="text-right text-slate-600">{{ number_format($product->qty_sold) }}</td>
                                <td class="text-right font-semibold text-emerald-600">XAF {{ number_format($product->profit_total, 2) }}</td>
                                <td class="text-right">
                                    <span class="ui-badge {{ $product->margin >= 20 ? 'ui-badge-success' : ($product->margin >= 10 ? 'ui-badge-warning' : 'ui-badge-danger') }}">
                                        {{ number_format($product->margin, 1) }}%
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <style>
        @media print {
            .no-print, .ui-tabs, .ui-btn-primary { display: none !important; }
            body { background: white !important; font-size: 10pt; color: black !important; }
            .ui-page, .ui-page-container { padding: 0 !important; margin: 0 !important; width: 100% !important; max-width: none !important; }
            .ui-card { border: 1px solid #e2e8f0 !important; box-shadow: none !important; margin-bottom: 20px !important; page-break-inside: avoid; }
            .ui-kpi-card { border: 1px solid #e2e8f0 !important; padding: 10px !important; page-break-inside: avoid; }
            canvas { max-width: 100% !important; height: auto !important; }
            .ui-table { width: 100% !important; border-collapse: collapse !important; }
            .ui-table th, .ui-table td { border: 1px solid #e2e8f0 !important; padding: 8px !important; color: black !important; }
            .text-emerald-600 { color: #059669 !important; }
            .text-rose-600 { color: #e11d48 !important; }
            div[wire\:ignore] { display: block !important; }
        }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    @script
    <script>
        let trendChart = null;
        let categoryChart = null;

        function initCharts() {
            const trendCtx = document.getElementById('profitTrendChart');
            const categoryCtx = document.getElementById('categoryProfitChart');

            if (trendChart) trendChart.destroy();
            if (categoryChart) categoryChart.destroy();

            // Trend Chart
            const trendData = $wire.profitByDay;
            trendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendData.map(d => d.day),
                    datasets: [
                        {
                            label: 'Net Profit',
                            data: trendData.map(d => d.net_profit),
                            borderColor: '#3b82f6',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2.5,
                            pointRadius: 4,
                            pointBackgroundColor: '#3b82f6'
                        },
                        {
                            label: 'Gross Profit',
                            data: trendData.map(d => d.profit),
                            borderColor: '#10b981',
                            fill: false,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#10b981'
                        },
                        {
                            label: 'Prev. Gross Profit',
                            data: trendData.map(d => d.prev_profit),
                            borderColor: '#94a3b8',
                            borderDash: [5, 5],
                            fill: false,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#94a3b8',
                            hidden: true
                        },
                        {
                            label: 'Revenue',
                            data: trendData.map(d => d.revenue),
                            borderColor: '#3b82f6',
                            borderDash: [2, 2],
                            fill: false,
                            tension: 0.4,
                            borderWidth: 1.5,
                            pointRadius: 0,
                            hidden: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'top',
                            align: 'end',
                            labels: {
                                boxWidth: 10,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': XAF ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { borderDash: [2, 2], color: '#f1f5f9' },
                            ticks: {
                                callback: value => 'XAF ' + value.toLocaleString()
                            }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });

            // Category Chart
            const catData = $wire.categoryProfit;
            categoryChart = new Chart(categoryCtx, {
                type: 'bar',
                data: {
                    labels: catData.map(d => d.name),
                    datasets: [{
                        label: 'Profit by Category',
                        data: catData.map(d => d.profit_total),
                        backgroundColor: '#6366f1',
                        borderRadius: 6
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Profit: XAF ' + context.parsed.x.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: { 
                            beginAtZero: true, 
                            grid: { borderDash: [2, 2], color: '#f1f5f9' },
                            ticks: {
                                callback: value => 'XAF ' + value.toLocaleString()
                            }
                        },
                        y: { grid: { display: false } }
                    }
                }
            });
        }

        initCharts();
        Livewire.on('updated', () => { setTimeout(initCharts, 100); });
        Livewire.on('updateCharts', () => { setTimeout(initCharts, 100); });
    </script>
    @endscript
</div>
