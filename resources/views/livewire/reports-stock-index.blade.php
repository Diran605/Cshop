<div class="ui-page">
    <div class="ui-page-container print-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Stock Report') }}</h2>
            <div class="ui-page-subtitle">{{ __('Stock levels, low stock alerts, and movement summary.') }}</div>
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
                        <a href="{{ route('reports.profit') }}" class="ui-btn-secondary">{{ __('Profit') }}</a>
                        <a href="{{ route('reports.stock') }}" class="ui-btn-primary">{{ __('Stock') }}</a>
                        <a href="{{ route('reports.expenses') }}" class="ui-btn-secondary">{{ __('Expenses') }}</a>
                        <a href="{{ route('reports.expiry') }}" class="ui-btn-secondary">{{ __('Expiry') }}</a>
                    </div>
                    <button type="button" onclick="window.print()" class="ui-btn-primary">{{ __('Print') }}</button>
                </div>

                <div class="mt-4 grid grid-cols-1 lg:grid-cols-6 gap-4">
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

                    <div class="flex items-end">
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="low_stock_only" class="ui-checkbox" />
                            <span class="ms-2 text-sm text-slate-700">{{ __('Low stock only') }}</span>
                        </label>
                    </div>

                    <div class="lg:col-span-2">
                        <label class="ui-label">{{ __('Search') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="mt-1 ui-input" placeholder="Search product name..." />
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-slate-500">{{ __('Products') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format((int) $totalProducts) }}</div>
                </div>
            </div>
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-slate-500">{{ __('Low Stock') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format((int) $lowStockCount) }}</div>
                </div>
            </div>
        </div>

        <div class="mt-6 ui-card">
            <div class="ui-card-body">
                <h3 class="ui-card-title">{{ __('Stock Levels') }}</h3>

                <div class="mt-4 overflow-x-auto">
                    <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th class="text-right">{{ __('Current') }}</th>
                                    <th class="text-right">{{ __('Min') }}</th>
                                    <th class="text-right">{{ __('Stock In Qty') }}</th>
                                    <th class="text-right">{{ __('Sold Qty') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($movementRows as $row)
                                    <tr>
                                        <td class="font-medium text-slate-900">{{ $row['product_name'] }}</td>
                                        <td class="text-right {{ (int) $row['current_stock'] <= (int) $row['minimum_stock'] ? 'text-red-700 font-semibold' : '' }}">{{ number_format((int) $row['current_stock']) }}</td>
                                        <td class="text-right">{{ number_format((int) $row['minimum_stock']) }}</td>
                                        <td class="text-right">{{ number_format((int) $row['stock_in_qty']) }}</td>
                                        <td class="text-right">{{ number_format((int) $row['sold_qty']) }}</td>
                                    </tr>
                                @endforeach

                                @if (count($movementRows) === 0)
                                    <tr>
                                        <td colspan="5" class="ui-table-empty">{{ __('No stock records found.') }}</td>
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
                <h3 class="ui-card-title">{{ __('Stock Trend') }}</h3>
                <div class="mt-1 text-sm text-slate-600">{{ __('Stock-in vs Sold (by day)') }}</div>

                @php
                    $trendMap = [];
                    foreach ($stockInByDay as $row) {
                        $trendMap[(string) $row->day] = [
                            'day' => (string) $row->day,
                            'stock_in_qty' => (int) ($row->stock_in_qty ?? 0),
                            'sold_qty' => 0,
                        ];
                    }
                    foreach ($soldByDay as $row) {
                        $day = (string) $row->day;
                        if (! isset($trendMap[$day])) {
                            $trendMap[$day] = [
                                'day' => $day,
                                'stock_in_qty' => 0,
                                'sold_qty' => (int) ($row->sold_qty ?? 0),
                            ];
                        } else {
                            $trendMap[$day]['sold_qty'] = (int) ($row->sold_qty ?? 0);
                        }
                    }
                    ksort($trendMap);
                    $trend = array_values($trendMap);

                    $trendLabels = array_map(fn ($r) => (string) $r['day'], $trend);
                    $trendIn = array_map(fn ($r) => (int) $r['stock_in_qty'], $trend);
                    $trendSold = array_map(fn ($r) => (int) $r['sold_qty'], $trend);
                @endphp

                @once
                    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
                @endonce

                <div class="mt-4">
                    <canvas id="stockTrendChart" height="100"></canvas>
                </div>

                <script>
                    (function () {
                        const el = document.getElementById('stockTrendChart');
                        if (!el || !window.Chart) return;

                        const labels = @json($trendLabels);
                        const stockIn = @json($trendIn);
                        const sold = @json($trendSold);

                        if (el._chart) {
                            el._chart.destroy();
                        }

                        el._chart = new Chart(el, {
                            type: 'bar',
                            data: {
                                labels,
                                datasets: [
                                    {
                                        label: 'Stock In',
                                        data: stockIn,
                                        backgroundColor: 'rgba(37, 99, 235, 0.25)',
                                        borderColor: '#2563EB',
                                        borderWidth: 1,
                                    },
                                    {
                                        label: 'Sold',
                                        data: sold,
                                        backgroundColor: 'rgba(220, 38, 38, 0.20)',
                                        borderColor: '#DC2626',
                                        borderWidth: 1,
                                    },
                                ]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
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

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Fast Moving Products') }}</h3>
                    <div class="mt-1 text-sm text-slate-600">{{ __('Top 10 by sold quantity') }}</div>

                    <div class="mt-4 overflow-x-auto">
                        <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th class="text-right">{{ __('Sold Qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($fastMoving as $row)
                                        <tr>
                                            <td class="font-medium text-slate-900">{{ $row['product_name'] }}</td>
                                            <td class="text-right">{{ number_format((int) $row['sold_qty']) }}</td>
                                        </tr>
                                    @endforeach
                                    @if (count($fastMoving) === 0)
                                        <tr>
                                            <td colspan="2" class="ui-table-empty">{{ __('No data found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Slow Moving Products') }}</h3>
                    <div class="mt-1 text-sm text-slate-600">{{ __('Bottom 10 (excluding zero sales)') }}</div>

                    <div class="mt-4 overflow-x-auto">
                        <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th class="text-right">{{ __('Sold Qty') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($slowMoving as $row)
                                        <tr>
                                            <td class="font-medium text-slate-900">{{ $row['product_name'] }}</td>
                                            <td class="text-right">{{ number_format((int) $row['sold_qty']) }}</td>
                                        </tr>
                                    @endforeach
                                    @if (count($slowMoving) === 0)
                                        <tr>
                                            <td colspan="2" class="ui-table-empty">{{ __('No data found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
