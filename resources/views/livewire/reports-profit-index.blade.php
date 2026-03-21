<div class="ui-page">
    <div class="ui-page-container print-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Profit Report') }}</h2>
            <div class="ui-page-subtitle">{{ __('Profit performance and margin overview.') }}</div>
        </div>

        <style>
            @media print {
                .no-print {
                    display: none !important;
                }

                .print-container {
                    max-width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
            }
        </style>

        <div class="ui-card no-print">
            <div class="ui-card-body">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="inline-flex items-center gap-2">
                        <a href="{{ route('reports.index') }}" class="ui-btn-secondary">{{ __('Sales') }}</a>
                        <a href="{{ route('reports.profit') }}" class="ui-btn-primary">{{ __('Profit') }}</a>
                        <a href="{{ route('reports.stock') }}" class="ui-btn-secondary">{{ __('Stock') }}</a>
                        <a href="{{ route('reports.expenses') }}" class="ui-btn-secondary">{{ __('Expenses') }}</a>
                        <a href="{{ route('reports.expiry') }}" class="ui-btn-secondary">{{ __('Expiry') }}</a>
                    </div>
                    <button type="button" onclick="window.print()" class="ui-btn-primary">{{ __('Print') }}</button>
                </div>

                <div class="mt-4 grid grid-cols-1 lg:grid-cols-7 gap-4">
                    <div>
                        <label class="ui-label">{{ __('Branch') }}</label>
                        @if ($isSuperAdmin)
                            <select wire:model="branch_id" class="mt-1 ui-select">
                                <option value="0">{{ __('All') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <div class="mt-1 rounded-lg border border-slate-300/80 bg-white/60 px-3 py-2 text-sm text-slate-700">
                                {{ $branches->first()?->name ?? '-' }}
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="ui-label">{{ __('From') }}</label>
                        <input type="date" wire:model="date_from" class="mt-1 ui-input" />
                    </div>

                    <div>
                        <label class="ui-label">{{ __('To') }}</label>
                        <input type="date" wire:model="date_to" class="mt-1 ui-input" />
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Category') }}</label>
                        <select wire:model="category_id" class="mt-1 ui-select">
                            <option value="0">{{ __('All') }}</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Product') }}</label>
                        <select wire:model="product_filter_id" class="mt-1 ui-select">
                            <option value="0">{{ __('All') }}</option>
                            @foreach ($productsForFilter as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Sale Mode') }}</label>
                        <select wire:model="sale_mode" class="mt-1 ui-select">
                            <option value="all">{{ __('All') }}</option>
                            <option value="unit">{{ __('Units') }}</option>
                            <option value="bulk">{{ __('Bulk') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Search') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="mt-1 ui-input" placeholder="Search product name..." />
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-xs text-slate-500">{{ __('Sales Total') }}</div>
                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ number_format((float) $salesTotal, 2) }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-xs text-slate-500">{{ __('COGS') }}</div>
                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ number_format((float) $cogsTotal, 2) }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-xs text-slate-500">{{ __('Gross Profit') }}</div>
                    <div class="mt-1 text-lg font-semibold text-emerald-600">{{ number_format((float) $profitTotal, 2) }}</div>
                    <div class="text-xs text-slate-500">{{ number_format((float) $profitMargin, 1) }}% margin</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-xs text-slate-500">{{ __('Expenses') }}</div>
                    <div class="mt-1 text-lg font-semibold text-orange-600">{{ number_format((float) $expenseTotal, 2) }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-xs text-slate-500">{{ __('Net Profit') }}</div>
                    <div class="mt-1 text-lg font-semibold {{ $netProfit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ number_format((float) $netProfit, 2) }}</div>
                    <div class="text-xs text-slate-500">{{ number_format((float) $netProfitMargin, 1) }}% margin</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-xs text-slate-500">{{ __('Sales Count') }}</div>
                    <div class="mt-1 text-lg font-semibold text-slate-900">{{ number_format((int) $salesCount) }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-xs text-slate-500">{{ __('Low Profit Lines') }}</div>
                    <div class="mt-1 text-lg font-semibold text-amber-600">{{ number_format((int) $lowProfitLines) }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-xs text-slate-500">{{ __('Loss Lines') }}</div>
                    <div class="mt-1 text-lg font-semibold text-red-600">{{ number_format((int) $lossLines) }}</div>
                </div>
            </div>
        </div>

        @if ($isSuperAdmin && $branch_id <= 0)
            <div class="mt-6 ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Profit by Branch') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Branch') }}</th>
                                        <th class="text-right">{{ __('Sales') }}</th>
                                        <th class="text-right">{{ __('COGS') }}</th>
                                        <th class="text-right">{{ __('Profit') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($branchesByProfit as $row)
                                        <tr>
                                            <td class="font-medium text-slate-900">{{ $row->branch_name }}</td>
                                            <td class="text-right">{{ number_format((float) $row->sales_total, 2) }}</td>
                                            <td class="text-right">{{ number_format((float) $row->cogs_total, 2) }}</td>
                                            <td class="text-right">{{ number_format((float) $row->profit_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    @if ($branchesByProfit->isEmpty())
                                        <tr>
                                            <td colspan="4" class="ui-table-empty">{{ __('No data found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="mt-6 ui-card">
            <div class="ui-card-body">
                <h3 class="ui-card-title">{{ __('Top Products by Profit') }}</h3>

                <div class="mt-4 overflow-x-auto">
                    <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th class="text-right">{{ __('Qty Sold') }}</th>
                                    <th class="text-right">{{ __('Sales') }}</th>
                                    <th class="text-right">{{ __('COGS') }}</th>
                                    <th class="text-right">{{ __('Profit') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($topProductsByProfit as $row)
                                    <tr>
                                        <td class="font-medium text-slate-900">{{ $row->product_name }}</td>
                                        <td class="text-right">{{ number_format((int) $row->qty_sold) }}</td>
                                        <td class="text-right">{{ number_format((float) $row->sales_total, 2) }}</td>
                                        <td class="text-right">{{ number_format((float) $row->cogs_total, 2) }}</td>
                                        <td class="text-right">{{ number_format((float) $row->profit_total, 2) }}</td>
                                    </tr>
                                @endforeach
                                @if ($topProductsByProfit->isEmpty())
                                    <tr>
                                        <td colspan="5" class="ui-table-empty">{{ __('No data found.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 ui-card no-print">
            <div class="ui-card-body">
                <h3 class="ui-card-title">{{ __('Profit Trend') }}</h3>
                <div class="mt-1 text-sm text-slate-600">{{ __('Revenue vs COGS vs Profit (by day)') }}</div>

                @php
                    $trendLabels = [];
                    $trendRevenue = [];
                    $trendCogs = [];
                    $trendProfit = [];

                    foreach ($profitByDay as $row) {
                        $trendLabels[] = (string) $row->day;
                        $trendRevenue[] = (float) ($row->sales_total ?? 0);
                        $trendCogs[] = (float) ($row->cogs_total ?? 0);
                        $trendProfit[] = (float) ($row->profit_total ?? 0);
                    }
                @endphp

                @once
                    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
                @endonce

                <div class="mt-4">
                    <canvas id="profitTrendChart" height="100"></canvas>
                </div>

                <script>
                    (function () {
                        const el = document.getElementById('profitTrendChart');
                        if (!el || !window.Chart) return;

                        const labels = @json($trendLabels);
                        const revenue = @json($trendRevenue);
                        const cogs = @json($trendCogs);
                        const profit = @json($trendProfit);

                        if (el._chart) {
                            el._chart.destroy();
                        }

                        el._chart = new Chart(el, {
                            type: 'line',
                            data: {
                                labels,
                                datasets: [
                                    {
                                        label: 'Revenue',
                                        data: revenue,
                                        borderColor: '#2563EB',
                                        backgroundColor: 'rgba(37, 99, 235, 0.12)',
                                        tension: 0.3,
                                        fill: true,
                                    },
                                    {
                                        label: 'COGS',
                                        data: cogs,
                                        borderColor: '#DC2626',
                                        backgroundColor: 'rgba(220, 38, 38, 0.10)',
                                        tension: 0.3,
                                        fill: true,
                                    },
                                    {
                                        label: 'Profit',
                                        data: profit,
                                        borderColor: '#16A34A',
                                        backgroundColor: 'rgba(22, 163, 74, 0.10)',
                                        tension: 0.3,
                                        fill: true,
                                    },
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: { position: 'bottom' },
                                },
                                scales: {
                                    y: { beginAtZero: true }
                                }
                            }
                        });
                    })();
                </script>
            </div>
        </div>
    </div>
</div>
