<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Reports') }}
            </h2>
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
                <div class="grid grid-cols-1 lg:grid-cols-7 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Branch') }}</label>
                        @if ($isSuperAdmin)
                            <select wire:model="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="0">{{ __('Select...') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <div class="mt-1 rounded-md border border-gray-300 bg-gray-50 px-3 py-2 text-sm text-gray-700">
                                {{ $branches->first()?->name ?? '-' }}
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('From') }}</label>
                        <input type="date" wire:model="date_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('To') }}</label>
                        <input type="date" wire:model="date_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Category') }}</label>
                        <select wire:model="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="0">{{ __('All') }}</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Product') }}</label>
                        <select wire:model="product_filter_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="0">{{ __('All') }}</option>
                            @foreach ($productsForFilter as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Sale Mode') }}</label>
                        <select wire:model="sale_mode" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="all">{{ __('All') }}</option>
                            <option value="unit">{{ __('Units') }}</option>
                            <option value="bulk">{{ __('Bulk') }}</option>
                        </select>
                    </div>

                    <div class="flex items-end">
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="low_stock_only" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <span class="ms-2 text-sm text-gray-700">{{ __('Low stock only') }}</span>
                        </label>
                    </div>
                </div>

                <div class="mt-4 flex items-end justify-between gap-4">
                    <div class="w-full max-w-md">
                        <label class="block text-sm font-medium text-gray-700">{{ __('Search (Products)') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="Search product name..." />
                    </div>
                    <div class="flex items-center justify-end">
                        <button type="button" onclick="window.print()" class="ui-btn-primary">
                            {{ __('Print') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-6">
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-gray-500">{{ __('Sales Count') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format((int) $salesCount) }}</div>
                    <div class="mt-2 text-sm text-gray-600">{{ __('Within selected date range') }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-gray-500">{{ __('Sales Total') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format((float) $salesTotal, 2) }}</div>
                    <div class="mt-2 text-sm text-gray-600">{{ __('Gross revenue (grand total)') }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-gray-500">{{ __('Items Sold') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format((int) $itemsSold) }}</div>
                    <div class="mt-2 text-sm text-gray-600">{{ __('Total quantity sold') }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-gray-500">{{ __('Avg Transaction') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format((float) $avgTransaction, 2) }}</div>
                    <div class="mt-2 text-sm text-gray-600">{{ __('Sales total / sales count') }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-gray-500">{{ __('COGS') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format((float) $cogsTotal, 2) }}</div>
                    <div class="mt-2 text-sm text-gray-600">{{ __('Cost of goods sold') }}</div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-gray-500">{{ __('Profit') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format((float) $profitTotal, 2) }}</div>
                    <div class="mt-2 text-sm text-gray-600">{{ __('Gross profit') }}</div>
                </div>
            </div>
        </div>

        <div class="mt-6 ui-card no-print">
            <div class="ui-card-body">
                <h3 class="ui-card-title">{{ __('Trends') }}</h3>
                <div class="mt-1 text-sm text-gray-600">{{ __('Revenue vs COGS vs Profit (by day)') }}</div>

                <div class="mt-4">
                    <canvas id="salesTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>

        @php
            $trendLabels = [];
            $trendRevenue = [];
            $trendCogs = [];
            $trendProfit = [];
            $trendSoldQty = [];

            foreach ($salesByDay as $row) {
                $trendLabels[] = (string) $row->day;
                $trendRevenue[] = (float) ($row->sales_total ?? 0);
                $trendCogs[] = (float) ($row->cogs_total ?? 0);
                $trendProfit[] = (float) ($row->profit_total ?? 0);
                $trendSoldQty[] = (int) ($row->sold_qty ?? 0);
            }
        @endphp

        @once
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        @endonce

        <script>
            (function () {
                const el = document.getElementById('salesTrendChart');
                if (!el || !window.Chart) return;

                const labels = @json($trendLabels);
                const revenue = @json($trendRevenue);
                const cogs = @json($trendCogs);
                const profit = @json($trendProfit);
                const soldQty = @json($trendSoldQty);

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
                                borderColor: '#4F46E5',
                                backgroundColor: 'rgba(79, 70, 229, 0.12)',
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
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        const v = (ctx.parsed.y ?? 0);
                                        return ctx.dataset.label + ': ' + v.toFixed(2);
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                ticks: {
                                    callback: function (v) { return Number(v).toFixed(2); }
                                }
                            }
                        }
                    }
                });

                const barEl = document.getElementById('salesTrendBarChart');
                if (barEl) {
                    if (barEl._chart) {
                        barEl._chart.destroy();
                    }

                    barEl._chart = new Chart(barEl, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [
                                {
                                    label: 'Revenue',
                                    data: revenue,
                                    backgroundColor: 'rgba(79, 70, 229, 0.65)',
                                },
                                {
                                    label: 'COGS',
                                    data: cogs,
                                    backgroundColor: 'rgba(220, 38, 38, 0.55)',
                                },
                                {
                                    label: 'Profit',
                                    data: profit,
                                    backgroundColor: 'rgba(22, 163, 74, 0.55)',
                                },
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            plugins: { legend: { position: 'bottom' } },
                            scales: {
                                x: { stacked: false },
                                y: {
                                    beginAtZero: true,
                                    ticks: { callback: function (v) { return Number(v).toFixed(2); } }
                                }
                            }
                        }
                    });
                }

                const qtyEl = document.getElementById('salesQtyBarChart');
                if (qtyEl) {
                    if (qtyEl._chart) {
                        qtyEl._chart.destroy();
                    }

                    qtyEl._chart = new Chart(qtyEl, {
                        type: 'bar',
                        data: {
                            labels,
                            datasets: [
                                {
                                    label: 'Items Sold (Units)',
                                    data: soldQty,
                                    backgroundColor: 'rgba(99, 102, 241, 0.35)',
                                    borderColor: '#4F46E5',
                                    borderWidth: 1,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { position: 'bottom' } },
                            scales: {
                                y: { beginAtZero: true }
                            }
                        }
                    });
                }
            })();
        </script>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6 no-print">
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Daily Bars') }}</h3>
                    <div class="mt-1 text-sm text-gray-600">{{ __('Revenue / COGS / Profit (by day)') }}</div>

                    <div class="mt-4" style="height: 320px;">
                        <canvas id="salesTrendBarChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Quantity Bars') }}</h3>
                    <div class="mt-1 text-sm text-gray-600">{{ __('Items sold per day') }}</div>

                    <div class="mt-4" style="height: 320px;">
                        <canvas id="salesQtyBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Top Products') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Qty Sold') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Amount') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($topProducts as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row->product_name }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ (int) $row->qty_sold }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ number_format((float) $row->amount_sold, 2) }}</td>
                                    </tr>
                                @endforeach

                                @if ($topProducts->isEmpty())
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No sales data for this period.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Inventory') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Current') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Min') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($inventory as $stock)
                                    <tr wire:key="inventory-{{ $stock->id }}">
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $stock->product?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-sm {{ (int) $stock->current_stock <= (int) $stock->minimum_stock ? 'text-red-700' : 'text-gray-700' }}">{{ $stock->current_stock }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ $stock->minimum_stock }}</td>
                                    </tr>
                                @endforeach

                                @if ($inventory->isEmpty())
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No inventory rows found for this branch.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Sales By Day') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Day') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Sales') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($salesByDay as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row->day }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ (int) $row->sales_count }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ number_format((float) $row->sales_total, 2) }}</td>
                                    </tr>
                                @endforeach

                                @if ($salesByDay->isEmpty())
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No sales for this period.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Stock In vs Sales (By Day)') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Day') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Stock In Qty') }}</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Sold Qty') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($movementByDay as $row)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row['day'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ (int) $row['stock_in_qty'] }}</td>
                                        <td class="px-4 py-3 text-sm text-gray-700">{{ (int) $row['sold_qty'] }}</td>
                                    </tr>
                                @endforeach

                                @if (count($movementByDay) === 0)
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No movement for this period.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 ui-card">
            <div class="ui-card-body">
                <h3 class="ui-card-title">{{ __('Per-Product Movement') }}</h3>
                <div class="mt-1 text-sm text-gray-600">{{ __('Totals for selected branch and date range.') }}</div>

                <div class="mt-4 overflow-x-auto">
                    <table class="ui-table">
                        <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Stock In Qty') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Sold Qty') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($movementRows as $row)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $row['product_name'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $row['stock_in_qty'] }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $row['sold_qty'] }}</td>
                                </tr>
                            @endforeach

                            @if (count($movementRows) === 0)
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No movement for this period.') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
