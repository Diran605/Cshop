<div class="ui-page">
    <div class="ui-page-container print-container">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="ui-page-title">{{ __('Expense Report') }}</h1>
                <p class="ui-page-subtitle">{{ __('Monitor operational costs and expense trends.') }}</p>
            </div>
            <div class="flex items-center gap-3 no-print">
                <div class="ui-tabs">
                    <a href="{{ route('reports.index') }}" class="ui-tab">{{ __('Sales') }}</a>
                    <a href="{{ route('reports.profit') }}" class="ui-tab">{{ __('Profit') }}</a>
                    <a href="{{ route('reports.stock') }}" class="ui-tab">{{ __('Stock') }}</a>
                    <a href="{{ route('reports.expenses') }}" class="ui-tab ui-tab-active">{{ __('Expenses') }}</a>
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

                    <!-- Search -->
                    <div>
                        <label class="ui-label mb-1">{{ __('Search Expenses') }}</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by description, type..." class="ui-input pl-10">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Total Expense Amount -->
            <div class="ui-kpi-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="ui-kpi-title">{{ __('Total Expenses') }}</div>
                    <div class="flex items-center gap-1 {{ $expenseTotalChange <= 0 ? 'text-emerald-600' : 'text-rose-600' }} text-xs font-bold bg-slate-50 px-2 py-1 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $expenseTotalChange <= 0 ? 'M19 14l-7 7m0 0l-7-7m7 7V3' : 'M5 10l7-7m0 0l7 7m-7-7v18' }}" />
                        </svg>
                        {{ abs(round($expenseTotalChange, 1)) }}%
                    </div>
                </div>
                <div class="ui-kpi-value">XAF {{ number_format($expenseTotal, 2) }}</div>
                <div class="text-xs text-slate-400 mt-1">{{ __('vs previous period') }}</div>
            </div>

            <!-- Expense Count -->
            <div class="ui-kpi-card">
                <div class="flex items-center justify-between mb-2">
                    <div class="ui-kpi-title">{{ __('Transaction Count') }}</div>
                    <div class="flex items-center gap-1 {{ $expenseCountChange <= 0 ? 'text-emerald-600' : 'text-rose-600' }} text-xs font-bold bg-slate-50 px-2 py-1 rounded-full">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $expenseCountChange <= 0 ? 'M19 14l-7 7m0 0l-7-7m7 7V3' : 'M5 10l7-7m0 0l7 7m-7-7v18' }}" />
                        </svg>
                        {{ abs(round($expenseCountChange, 1)) }}%
                    </div>
                </div>
                <div class="ui-kpi-value">{{ number_format($expenseCount) }}</div>
                <div class="text-xs text-slate-400 mt-1">{{ __('vs previous period') }}</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
            <!-- Expense Trend -->
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title mb-6">{{ __('Expense Trend') }}</h3>
                    <div class="h-80" wire:ignore>
                        <canvas id="expenseTrendChart" wire:key="expense-trend-canvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Expense by Type -->
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title mb-6">{{ __('Expenses by Category') }}</h3>
                    <div class="h-80" wire:ignore>
                        <canvas id="expenseCategoryChart" wire:key="expense-category-canvas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expense Log Table -->
        <div class="ui-card overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="ui-card-title">{{ __('Recent Expense Log') }}</h3>
                <span class="ui-badge bg-blue-50 text-blue-700 ring-blue-200">
                    {{ __('Last 10 Transactions') }}
                </span>
            </div>
            <div class="ui-table-wrap border-0 rounded-none shadow-none">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Reference') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Description') }}</th>
                            <th class="text-right">{{ __('Amount') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenseLog as $expense)
                        <tr>
                            <td class="text-slate-600">{{ $expense->expense_date->format('M d, Y') }}</td>
                            <td class="font-medium text-slate-900">{{ $expense->expense_no }}</td>
                            <td>
                                <span class="ui-badge bg-slate-100 text-slate-700 ring-slate-200">
                                    {{ $expense->expense_type }}
                                </span>
                            </td>
                            <td class="text-slate-500">{{ Str::limit($expense->description, 40) }}</td>
                            <td class="text-right font-bold text-slate-900">XAF {{ number_format($expense->amount, 2) }}</td>
                        </tr>
                        @endforeach
                        @if($expenseLog->isEmpty())
                        <tr>
                            <td colspan="5" class="ui-table-empty">
                                {{ __('No expenses found for this period.') }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
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
            const trendCtx = document.getElementById('expenseTrendChart');
            const categoryCtx = document.getElementById('expenseCategoryChart');

            if (trendChart) trendChart.destroy();
            if (categoryChart) categoryChart.destroy();

            // Trend Chart
            const trendData = $wire.expensesByDay;
            trendChart = new Chart(trendCtx, {
                type: 'line',
                data: {
                    labels: trendData.map(d => d.day),
                    datasets: [
                        {
                            label: 'Current Period',
                            data: trendData.map(d => d.amount),
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#ef4444'
                        },
                        {
                            label: 'Previous Period',
                            data: trendData.map(d => d.prev_amount),
                            borderColor: '#94a3b8',
                            backgroundColor: 'transparent',
                            borderDash: [5, 5],
                            fill: false,
                            tension: 0.4,
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: '#94a3b8'
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
            const catData = $wire.expensesByType;
            categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: catData.map(d => d.expense_type),
                    datasets: [{
                        data: catData.map(d => d.amount_total),
                        backgroundColor: [
                            '#6366f1', '#10b981', '#f59e0b', '#ef4444', '#3b82f6',
                            '#8b5cf6', '#ec4899', '#06b6d4', '#f97316', '#14b8a6'
                        ],
                        borderWidth: 0,
                        hoverOffset: 20
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
                    },
                    cutout: '60%'
                }
            });
        }

        initCharts();
        Livewire.on('updateCharts', () => { setTimeout(initCharts, 100); });
        Livewire.on('updated', () => { setTimeout(initCharts, 100); });
    </script>
    @endscript
</div>

