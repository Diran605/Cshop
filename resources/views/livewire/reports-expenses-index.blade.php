<div class="ui-page">
    <div class="ui-page-container print-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Expense Report') }}</h2>
            <div class="ui-page-subtitle">{{ __('Expense breakdown and monthly operational costs.') }}</div>
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
                        <a href="{{ route('reports.stock') }}" class="ui-btn-secondary">{{ __('Stock') }}</a>
                        <a href="{{ route('reports.expenses') }}" class="ui-btn-primary">{{ __('Expenses') }}</a>
                        <a href="{{ route('reports.expiry') }}" class="ui-btn-secondary">{{ __('Expiry') }}</a>
                    </div>
                    <button type="button" onclick="window.print()" class="ui-btn-primary">{{ __('Print') }}</button>
                </div>

                <div class="mt-4 grid grid-cols-1 lg:grid-cols-5 gap-4">
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
                        <label class="ui-label">{{ __('Status') }}</label>
                        <select wire:model="expense_status" class="mt-1 ui-select">
                            <option value="active">{{ __('Active') }}</option>
                            <option value="voided">{{ __('Voided') }}</option>
                            <option value="all">{{ __('All') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Search') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="mt-1 ui-input" placeholder="Search expense..." />
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-2 gap-6">
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-slate-500">{{ __('Expense Count') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format((int) $expenseCount) }}</div>
                </div>
            </div>
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-slate-500">{{ __('Expense Total') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format((float) $expenseTotal, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="mt-6 ui-card">
            <div class="ui-card-body">
                <h3 class="ui-card-title">{{ __('Expense Breakdown by Category') }}</h3>

                <div class="mt-4 overflow-x-auto">
                    <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Type') }}</th>
                                    <th class="text-right">{{ __('Count') }}</th>
                                    <th class="text-right">{{ __('Total') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expensesByType as $row)
                                    <tr>
                                        <td class="font-medium text-slate-900">{{ $row->expense_type }}</td>
                                        <td class="text-right">{{ number_format((int) $row->expense_count) }}</td>
                                        <td class="text-right">{{ number_format((float) $row->amount_total, 2) }}</td>
                                    </tr>
                                @endforeach
                                @if ($expensesByType->isEmpty())
                                    <tr>
                                        <td colspan="3" class="ui-table-empty">{{ __('No data found.') }}</td>
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
                <h3 class="ui-card-title">{{ __('Expense Trend (by day)') }}</h3>

                @php
                    $trendLabels = [];
                    $trendAmounts = [];
                    foreach ($expensesByDay as $row) {
                        $trendLabels[] = (string) $row->day;
                        $trendAmounts[] = (float) ($row->amount_total ?? 0);
                    }
                @endphp

                @once
                    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
                @endonce

                <div class="mt-4">
                    <canvas id="expenseTrendChart" height="100"></canvas>
                </div>

                <script>
                    (function () {
                        const el = document.getElementById('expenseTrendChart');
                        if (!el || !window.Chart) return;

                        const labels = @json($trendLabels);
                        const amounts = @json($trendAmounts);

                        if (el._chart) {
                            el._chart.destroy();
                        }

                        el._chart = new Chart(el, {
                            type: 'line',
                            data: {
                                labels,
                                datasets: [
                                    {
                                        label: 'Expenses',
                                        data: amounts,
                                        borderColor: '#2563EB',
                                        backgroundColor: 'rgba(37, 99, 235, 0.12)',
                                        tension: 0.3,
                                        fill: true,
                                    }
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
    </div>
</div>
