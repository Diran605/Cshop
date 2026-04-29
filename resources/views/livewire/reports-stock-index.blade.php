<div class="ui-page">
    <div class="ui-page-container print-container">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="ui-page-title">{{ __('Stock Report') }}</h1>
                <p class="ui-page-subtitle">{{ __('Monitor inventory levels and movement.') }}</p>
            </div>
            <div class="flex items-center gap-3 no-print">
                <div class="ui-tabs">
                    <a href="{{ route('reports.index') }}" class="ui-tab">{{ __('Sales') }}</a>
                    <a href="{{ route('reports.profit') }}" class="ui-tab">{{ __('Profit') }}</a>
                    <a href="{{ route('reports.stock') }}" class="ui-tab ui-tab-active">{{ __('Stock') }}</a>
                    <a href="{{ route('reports.expenses') }}" class="ui-tab">{{ __('Expenses') }}</a>
                    <a href="{{ route('reports.expiry') }}" class="ui-tab">{{ __('Expiry') }}</a>
                    <a href="{{ route('clearance.reports') }}" class="ui-tab">{{ __('Clearance') }}</a>
                    <a href="{{ route('daily_summary.index') }}" class="ui-tab">{{ __('Summary') }}</a>
                    <a href="{{ route('stock_valuation.index') }}" class="ui-tab">{{ __('Valuation') }}</a>
                </div>
                <button onclick="window.print()" class="ui-btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    {{ __('Print') }}
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="ui-card mb-8 no-print">
            <div class="ui-card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Date Range -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="ui-label mb-1">{{ __('From') }}</label>
                            <input type="date" wire:model.live="date_from" class="ui-input">
                        </div>
                        <div>
                            <label class="ui-label mb-1">{{ __('To') }}</label>
                            <input type="date" wire:model.live="date_to" class="ui-input">
                        </div>
                    </div>

                    <!-- Branch Selection -->
                    <div>
                        <label class="ui-label mb-1">{{ __('Branch') }}</label>
                        @if ($isSuperAdmin)
                            <select wire:model.live="branch_id" class="ui-select">
                                <option value="0">{{ __('All Branches') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <div class="ui-input bg-slate-50 text-slate-600">
                                {{ $branches->first()?->name ?? __('My Branch') }}
                            </div>
                        @endif
                    </div>

                    <!-- Category Selection -->
                    <div>
                        <label class="ui-label mb-1">{{ __('Category') }}</label>
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
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Stock In -->
            <div class="ui-kpi-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="ui-kpi-title">{{ __('Stock In') }}</div>
                    <div class="flex items-center gap-1 {{ $stockInChange >= 0 ? 'text-emerald-600' : 'text-rose-600' }} text-xs font-bold bg-slate-50 px-2 py-1 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $stockInChange >= 0 ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}" />
                        </svg>
                        {{ abs(round($stockInChange, 1)) }}%
                    </div>
                </div>
                <div class="ui-kpi-value">{{ number_format($currentStockIn) }}</div>
                <div class="text-xs text-slate-400 mt-1">{{ __('vs previous period') }}</div>
            </div>

            <!-- Sold Qty -->
            <div class="ui-kpi-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="ui-kpi-title">{{ __('Items Sold') }}</div>
                    <div class="flex items-center gap-1 {{ $soldChange >= 0 ? 'text-emerald-600' : 'text-rose-600' }} text-xs font-bold bg-slate-50 px-2 py-1 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $soldChange >= 0 ? 'M5 10l7-7m0 0l7 7m-7-7v18' : 'M19 14l-7 7m0 0l-7-7m7 7V3' }}" />
                        </svg>
                        {{ abs(round($soldChange, 1)) }}%
                    </div>
                </div>
                <div class="ui-kpi-value">{{ number_format($currentSold) }}</div>
                <div class="text-xs text-slate-400 mt-1">{{ __('vs previous period') }}</div>
            </div>

            <!-- Current Value -->
            <div class="ui-kpi-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="ui-kpi-title">{{ __('Current Stock Value') }}</div>
                </div>
                <div class="ui-kpi-value text-green-600">XAF {{ number_format($metrics->total_value, 2) }}</div>
                <div class="text-xs text-slate-400 mt-1">{{ __('Based on cost price') }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Total Items -->
            <div class="ui-kpi-card border-l-4 border-l-blue-500">
                <div class="ui-kpi-title">{{ __('Total Unique Items') }}</div>
                <div class="ui-kpi-value mt-2">{{ number_format($metrics->total_items) }}</div>
            </div>

            <!-- Low Stock -->
            <div class="ui-kpi-card border-l-4 border-l-amber-500">
                <div class="ui-kpi-title">{{ __('Low Stock') }}</div>
                <div class="mt-2 flex items-center justify-between">
                    <div class="ui-kpi-value text-amber-600">{{ number_format($metrics->low_stock) }}</div>
                    @if($metrics->low_stock > 0)
                        <span class="flex h-3 w-3 rounded-full bg-amber-500 animate-pulse"></span>
                    @endif
                </div>
            </div>

            <!-- Out of Stock -->
            <div class="ui-kpi-card border-l-4 border-l-rose-500">
                <div class="ui-kpi-title">{{ __('Out of Stock') }}</div>
                <div class="mt-2 flex items-center justify-between">
                    <div class="ui-kpi-value text-rose-600">{{ number_format($metrics->out_of_stock) }}</div>
                    @if($metrics->out_of_stock > 0)
                        <span class="flex h-3 w-3 rounded-full bg-rose-500 animate-pulse"></span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Stock Movement -->
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title mb-6">{{ __('Stock Movement') }}</h3>
                    <div class="h-80" wire:ignore>
                        <canvas id="stockMovementChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Category Breakdown -->
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title mb-6">{{ __('Stock by Category') }}</h3>
                    <div class="h-80" wire:ignore>
                        <canvas id="stockCategoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attention List Table -->
        <div class="ui-card overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="ui-card-title">{{ __('Low Stock Attention List') }}</h3>
                <span class="ui-badge-warning">
                    {{ __('Top 10 Critical Items') }}
                </span>
            </div>
            <div class="ui-table-wrap border-0 rounded-none shadow-none">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th class="text-right">{{ __('Min') }}</th>
                            <th class="text-right">{{ __('Current') }}</th>
                            <th class="text-right">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attentionList as $item)
                        <tr>
                            <td>
                                <div class="font-medium text-slate-900">{{ $item->product_name }}</div>
                            </td>
                            <td>{{ $item->product?->category?->name }}</td>
                            <td class="text-right">{{ number_format($item->minimum_stock) }}</td>
                            <td class="text-right font-semibold {{ $item->current_stock <= 0 ? 'text-rose-600' : 'text-amber-600' }}">
                                {{ number_format($item->current_stock) }} {{ $item->product?->unitType?->name }}
                            </td>
                            <td class="text-right">
                                <span class="{{ $item->current_stock <= 0 ? 'ui-badge-danger' : 'ui-badge-warning' }}">
                                    {{ $item->current_stock <= 0 ? __('Out of Stock') : __('Low Stock') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                        @if($attentionList->isEmpty())
                        <tr>
                            <td colspan="5" class="ui-table-empty">
                                {{ __('All items are above minimum stock levels.') }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    @script
    <script>
        let movementChart = null;
        let categoryChart = null;

        function initCharts() {
            const movementCtx = document.getElementById('stockMovementChart');
            const categoryCtx = document.getElementById('stockCategoryChart');

            if (movementChart) movementChart.destroy();
            if (categoryChart) categoryChart.destroy();

            // Movement Chart (Trend)
            const trendData = @json($trendData);
            movementChart = new Chart(movementCtx, {
                type: 'bar',
                data: {
                    labels: trendData.map(d => d.day),
                    datasets: [
                        {
                            label: 'Stock In',
                            data: trendData.map(d => d.in),
                            backgroundColor: '#3b82f6',
                            borderRadius: 4
                        },
                        {
                            label: 'Sold',
                            data: trendData.map(d => d.out),
                            backgroundColor: '#f43f5e',
                            borderRadius: 4
                        },
                        {
                            label: 'Prev. Stock In',
                            data: trendData.map(d => d.prev_in),
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            borderRadius: 4,
                            hidden: true
                        },
                        {
                            label: 'Prev. Sold',
                            data: trendData.map(d => d.prev_out),
                            backgroundColor: 'rgba(244, 63, 94, 0.2)',
                            borderRadius: 4,
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
                                    return context.dataset.label + ': ' + context.parsed.y.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        y: { 
                            beginAtZero: true, 
                            grid: { borderDash: [2, 2], color: '#f1f5f9' },
                            ticks: {
                                callback: value => value.toLocaleString()
                            }
                        },
                        x: { grid: { display: false } }
                    }
                }
            });

            // Category Chart (Breakdown)
            const catData = @json($categoryStock);
            categoryChart = new Chart(categoryCtx, {
                type: 'pie',
                data: {
                    labels: catData.map(d => d.name),
                    datasets: [{
                        data: catData.map(d => d.stock_value),
                        backgroundColor: [
                            '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#3b82f6',
                            '#8b5cf6', '#ec4899', '#06b6d4', '#f97316', '#14b8a6'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            position: 'right',
                            labels: {
                                boxWidth: 10,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': XAF ' + context.parsed.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }

        initCharts();
        Livewire.on('updated', () => { setTimeout(initCharts, 100); });
    </script>
    @endscript
</div>

