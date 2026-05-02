<div class="ui-page">
    <div class="ui-page-container print-container">
        <div class="mb-6">
            <h2 class="ui-page-title text-2xl font-bold text-slate-900">{{ __('Sales Report') }}</h2>
            <div class="ui-page-subtitle text-slate-500">{{ __('Revenue performance, transaction volume, and product ranking') }}</div>
        </div>

        <div class="ui-card no-print mb-6">
            <div class="ui-card-body">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                    <div class="ui-tabs">
                        <a href="{{ route('reports.index') }}" class="ui-tab ui-tab-active">{{ __('Sales') }}</a>
                        <a href="{{ route('reports.profit') }}" class="ui-tab">{{ __('Profit') }}</a>
                        <a href="{{ route('reports.stock') }}" class="ui-tab">{{ __('Stock') }}</a>
                        <a href="{{ route('reports.expenses') }}" class="ui-tab">{{ __('Expenses') }}</a>
                        <a href="{{ route('reports.expiry') }}" class="ui-tab">{{ __('Expiry') }}</a>
                        <a href="{{ route('clearance.reports') }}" class="ui-tab">{{ __('Clearance') }}</a>
                        <a href="{{ route('daily_summary.index') }}" class="ui-tab">{{ __('Summary') }}</a>
                        <a href="{{ route('stock_valuation.index') }}" class="ui-tab">{{ __('Valuation') }}</a>
                    </div>
                    <button type="button" onclick="window.print()" class="ui-btn-primary">
                        {{ __('Print') }}
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="ui-label text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1 block">{{ __('Period') }}</label>
                        <div class="flex gap-2">
                            <input type="date" wire:model.live="date_from" class="ui-input flex-1" />
                            <input type="date" wire:model.live="date_to" class="ui-input flex-1" />
                        </div>
                    </div>
                    <div>
                        <label class="ui-label text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1 block">{{ __('Branch') }}</label>
                        @if ($isSuperAdmin)
                            <select wire:model.live="branch_id" class="ui-select w-full">
                                <option value="0">{{ __('All Branches') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <div class="ui-input bg-slate-50 text-slate-500">{{ $branches->first()?->name ?? '-' }}</div>
                        @endif
                    </div>
                    <div>
                        <label class="ui-label text-xs font-semibold uppercase tracking-wider text-slate-500 mb-1 block">{{ __('Category') }}</label>
                        <select wire:model.live="category_id" class="ui-select w-full">
                            <option value="0">{{ __('All Categories') }}</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            @php
                $metrics = [
                    ['label' => __('Total Sales'), 'value' => number_format($salesTotal, 2), 'change' => $salesChange, 'prefix' => 'XAF '],
                    ['label' => __('Transactions'), 'value' => number_format($salesCount), 'change' => $countChange],
                    ['label' => __('Avg Basket'), 'value' => number_format($avgTransaction, 2), 'change' => $avgChange, 'prefix' => 'XAF '],
                    ['label' => __('Units Sold'), 'value' => number_format($itemsSold), 'change' => $itemsChange],
                ];
            @endphp
            @foreach($metrics as $metric)
            <div class="ui-kpi-card">
                <div class="ui-kpi-title">{{ $metric['label'] }}</div>
                <div class="mt-2 flex items-baseline justify-between">
                    <div class="ui-kpi-value">
                        {{ ($metric['prefix'] ?? '') . $metric['value'] }}
                    </div>
                    <div class="inline-flex items-center text-sm font-semibold {{ $metric['change'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        @if($metric['change'] >= 0)
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" /></svg>
                        @else
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" /></svg>
                        @endif
                        {{ abs(round($metric['change'], 1)) }}%
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <div class="ui-card mb-6">
            <div class="ui-card-body">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Sales Trend') }}</h3>
                    <select wire:model.live="trend_granularity" class="text-xs font-semibold uppercase tracking-wider text-slate-500 bg-transparent border-none focus:ring-0">
                        <option value="day">{{ __('Daily') }}</option>
                        <option value="week">{{ __('Weekly') }}</option>
                        <option value="month">{{ __('Monthly') }}</option>
                    </select>
                </div>
                <div class="h-80" wire:ignore>
                    <canvas id="salesTrendChart" wire:key="sales-trend-canvas"></canvas>
                </div>
            </div>
        </div>

        <div class="ui-card">
            <div class="ui-card-body p-0">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="text-lg font-bold text-slate-900">{{ __('Top Products') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="ui-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>{{ __('Product Name') }}</th>
                                <th>{{ __('Category') }}</th>
                                <th class="text-right">{{ __('Units Sold') }}</th>
                                <th class="text-right">{{ __('Revenue') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $index => $product)
                            <tr wire:key="top-product-{{ $product->product_id }}">
                                <td>{{ $index + 1 }}</td>
                                <td class="font-medium text-slate-900">{{ $product->product_name }}</td>
                                <td>
                                    <span class="ui-badge ui-badge-info">{{ $product->category_name }}</span>
                                </td>
                                <td class="text-right text-slate-900">{{ number_format($product->units_sold) }}</td>
                                <td class="text-right font-bold text-slate-900">XAF {{ number_format($product->revenue, 2) }}</td>
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
            /* Force show charts in print */
            div[wire\:ignore] { display: block !important; }
        }
    </style>

    @script
    <script>
        let salesChart = null;

        const initCharts = () => {
            const ctx = document.getElementById('salesTrendChart');
            if (!ctx) return;

            if (salesChart) salesChart.destroy();

            // Fetch the data from the Livewire component properties
            const trendData = $wire.salesByDay;
            const labels = trendData.map(d => d.label);
            const currentData = trendData.map(d => d.total);
            const prevData = trendData.map(d => d.prev_total);

            salesChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Current Period',
                            data: currentData,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#2563eb',
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Previous Period',
                            data: prevData,
                            borderColor: '#94a3b8',
                            backgroundColor: 'transparent',
                            borderDash: [5, 5],
                            fill: false,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#94a3b8',
                            pointHoverRadius: 6,
                            hidden: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: true,
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
                            backgroundColor: '#1e293b',
                            titleColor: '#fff',
                            bodyColor: '#fff',
                            padding: 12,
                            borderRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': XAF ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { color: '#64748b', font: { size: 11 } }
                        },
                        y: {
                            grid: { color: '#f1f5f9' },
                            ticks: { 
                                color: '#64748b', 
                                font: { size: 11 },
                                callback: value => 'XAF ' + value.toLocaleString()
                            }
                        }
                    }
                }
            });
        };

        initCharts();
        $wire.on('updateCharts', () => initCharts());
    </script>
    @endscript
</div>
