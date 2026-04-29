<div class="ui-page">
    <div class="ui-page-container print-container">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="ui-page-title">{{ __('Expiry Report') }}</h1>
                <p class="ui-page-subtitle">{{ __('Track product shelf life and minimize waste.') }}</p>
            </div>
            <div class="flex items-center gap-3 no-print">
                <div class="ui-tabs">
                    <a href="{{ route('reports.index') }}" class="ui-tab">{{ __('Sales') }}</a>
                    <a href="{{ route('reports.profit') }}" class="ui-tab">{{ __('Profit') }}</a>
                    <a href="{{ route('reports.stock') }}" class="ui-tab">{{ __('Stock') }}</a>
                    <a href="{{ route('reports.expenses') }}" class="ui-tab">{{ __('Expenses') }}</a>
                    <a href="{{ route('reports.expiry') }}" class="ui-tab ui-tab-active">{{ __('Expiry') }}</a>
                    <a href="{{ route('clearance.reports') }}" class="ui-tab">{{ __('Clearance') }}</a>
                    <a href="{{ route('daily_summary.index') }}" class="ui-tab">{{ __('Summary') }}</a>
                    <a href="{{ route('stock_valuation.index') }}" class="ui-tab">{{ __('Valuation') }}</a>
                </div>
                <button onclick="window.print()" class="ui-btn-primary">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    {{ __('Print') }}
                </button>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="ui-card mb-8 no-print">
            <div class="ui-card-body">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Days Ahead -->
                    <div>
                        <label class="ui-label mb-1">{{ __('Near-Expiry Window') }}</label>
                        <div class="relative">
                            <input type="number" wire:model.live="days_ahead" min="1" class="ui-input pr-12">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-text-secondary text-xs">
                                {{ __('Days') }}
                            </div>
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
                            <div class="w-full px-3 py-2 rounded-lg border border-gray-200 bg-slate-50 text-sm text-text-secondary">
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

                    <!-- Search -->
                    <div>
                        <label class="ui-label mb-1">{{ __('Search Products') }}</label>
                        <div class="relative">
                            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search product name..." class="ui-input pl-10">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-text-disabled" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Expired Count -->
            <div class="ui-kpi-card border-l-4 border-l-rose-500">
                <div class="ui-kpi-title">{{ __('Expired Batches') }}</div>
                <div class="mt-2 flex items-center justify-between">
                    <div class="ui-kpi-value text-rose-600">{{ number_format($expiredCount) }}</div>
                    @if($expiredCount > 0)
                        <span class="flex h-3 w-3 rounded-full bg-rose-500 animate-pulse"></span>
                    @endif
                </div>
            </div>

            <!-- Expired Loss -->
            <div class="ui-kpi-card">
                <div class="ui-kpi-title">{{ __('Total Expired Loss') }}</div>
                <div class="ui-kpi-value">XAF {{ number_format($expiredLoss, 2) }}</div>
            </div>

            <!-- Near-Expiry Count -->
            <div class="ui-kpi-card border-l-4 border-l-amber-500">
                <div class="ui-kpi-title">{{ __('Near-Expiry Batches') }}</div>
                <div class="mt-2 flex items-center justify-between">
                    <div class="ui-kpi-value text-amber-600">{{ number_format($nearExpiryCount) }}</div>
                    @if($nearExpiryCount > 0)
                        <span class="flex h-3 w-3 rounded-full bg-amber-500 animate-pulse"></span>
                    @endif
                </div>
            </div>

            <!-- Near-Expiry Value -->
            <div class="ui-kpi-card">
                <div class="ui-kpi-title">{{ __('Risk Exposure Value') }}</div>
                <div class="ui-kpi-value">XAF {{ number_format($nearExpiryValue, 2) }}</div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 gap-8 mb-8">
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title !px-0 mb-6">{{ __('Expiry Timeline (Next 4 Months)') }}</h3>
                    <div class="h-64" wire:ignore>
                        <canvas id="expiryTimelineChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Urgent Expiry Table -->
        <div class="ui-table-wrap mb-8">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <h3 class="ui-card-title !mt-0 !mb-0 !px-0">{{ __('Urgent Expiry & Expired Items') }}</h3>
                <span class="ui-badge-danger">
                    {{ __('Critical Attention Required') }}
                </span>
            </div>
            <div class="overflow-x-auto">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Batch Ref') }}</th>
                            <th>{{ __('Expiry Date') }}</th>
                            <th class="text-right">{{ __('Remaining') }}</th>
                            <th class="text-right">{{ __('Loss Risk') }}</th>
                            <th class="text-right">{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Expired First --}}
                        @foreach($expiredRows as $row)
                        <tr class="bg-rose-50/20">
                            <td>
                                <div class="font-medium text-text-primary">{{ $row->product_name }}</div>
                            </td>
                            <td class="text-text-secondary">{{ $row->batch_ref_no ?: '-' }}</td>
                            <td class="font-bold text-rose-600">{{ \Carbon\Carbon::parse($row->expiry_date)->format('M d, Y') }}</td>
                            <td class="text-right font-medium">{{ number_format($row->remaining_quantity) }}</td>
                            <td class="text-right font-bold text-rose-600">XAF {{ number_format($row->cost_price * $row->remaining_quantity, 2) }}</td>
                            <td class="text-right">
                                <span class="ui-badge-danger uppercase">
                                    {{ __('EXPIRED') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach

                        {{-- Near Expiry Next --}}
                        @foreach($nearExpiryRows as $row)
                        <tr>
                            <td>
                                <div class="font-medium text-text-primary">{{ $row->product_name }}</div>
                            </td>
                            <td class="text-text-secondary">{{ $row->batch_ref_no ?: '-' }}</td>
                            <td class="font-bold text-amber-600">{{ \Carbon\Carbon::parse($row->expiry_date)->format('M d, Y') }}</td>
                            <td class="text-right font-medium">{{ number_format($row->remaining_quantity) }}</td>
                            <td class="text-right font-bold text-amber-600">XAF {{ number_format($row->cost_price * $row->remaining_quantity, 2) }}</td>
                            <td class="text-right">
                                <span class="ui-badge-warning uppercase">
                                    {{ __('NEAR EXPIRY') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach

                        @if($expiredRows->isEmpty() && $nearExpiryRows->isEmpty())
                        <tr>
                            <td colspan="6" class="ui-table-empty">
                                {{ __('No urgent expiry items found.') }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

        @script
        <script>
            let timelineChart = null;

            function initCharts() {
                const ctx = document.getElementById('expiryTimelineChart');
                if (!ctx) return;
                if (timelineChart) timelineChart.destroy();

                const data = @json($timelineData);
                timelineChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: data.map(d => d.month),
                        datasets: [{
                            label: 'Items Expiring',
                            data: data.map(d => d.count),
                            backgroundColor: (context) => {
                                const index = context.dataIndex;
                                return index === 0 ? '#ef4444' : (index === 1 ? '#f59e0b' : '#3b82f6');
                            },
                            borderRadius: 6,
                            maxBarThickness: 60
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
                                        return context.parsed.y + ' items expiring';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: { 
                                beginAtZero: true, 
                                grid: { borderDash: [2, 2], color: '#f1f5f9' },
                                ticks: { stepSize: 1 } 
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            initCharts();
            Livewire.on('updated', () => { setTimeout(initCharts, 100); });
        </script>
        @endscript
    </div>
</div>
