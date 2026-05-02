<div class="p-6">
    <div class="flex justify-between items-center mb-6 no-print">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">{{ __('Business Overview Report') }}</h1>
            <p class="text-sm text-slate-500">{{ __('Comprehensive financial summary for the year') }} {{ $year }}</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center bg-white border rounded-lg overflow-hidden">
                <button wire:click="prevYear" class="px-3 py-2 hover:bg-slate-50 border-r">&larr;</button>
                <span class="px-4 font-bold">{{ $year }}</span>
                <button wire:click="nextYear" class="px-3 py-2 hover:bg-slate-50 border-l">&rarr;</button>
            </div>
            <button onclick="window.print()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                {{ __('Print Report') }}
            </button>
        </div>
    </div>

    <!-- Print Header -->
    <div class="hidden print:block mb-8 border-b pb-4">
        <h1 class="text-3xl font-bold text-center uppercase">{{ $data['branch_name'] }} - {{ __('Annual Report') }}</h1>
        <p class="text-center text-xl mt-2">{{ __('Financial Year:') }} {{ $year }}</p>
        <p class="text-center text-sm text-slate-500 mt-1">{{ __('Generated on:') }} {{ now()->format('d M Y H:i') }}</p>
    </div>

    @if($isSuperAdmin)
    <div class="mb-6 no-print">
        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('Filter by Branch') }}</label>
        <select wire:model.live="branch_id" class="w-full md:w-64 rounded-md border-slate-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            <option value="0">{{ __('All Branches') }}</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <!-- Top Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
        <div class="bg-white p-4 rounded-xl border shadow-sm">
            <p class="text-[10px] font-bold text-slate-500 uppercase mb-1">{{ __('Total Sales') }}</p>
            <h3 class="text-xl font-bold text-slate-900">{{ number_format($data['total_sales'], 2) }}</h3>
        </div>
        <div class="bg-white p-4 rounded-xl border shadow-sm border-l-4 border-l-green-500">
            <p class="text-[10px] font-bold text-green-600 uppercase mb-1">{{ __('Gross Profit') }}</p>
            <h3 class="text-xl font-bold text-slate-900">{{ number_format($data['total_profit'], 2) }}</h3>
        </div>
        <div class="bg-white p-4 rounded-xl border shadow-sm border-l-4 border-l-rose-500">
            <p class="text-[10px] font-bold text-rose-600 uppercase mb-1">{{ __('Inventory Loss') }}</p>
            <h3 class="text-xl font-bold text-slate-900">{{ number_format($data['total_inventory_loss'], 2) }}</h3>
        </div>
        <div class="bg-white p-4 rounded-xl border shadow-sm border-l-4 border-l-red-500">
            <p class="text-[10px] font-bold text-red-600 uppercase mb-1">{{ __('Total Expenses') }}</p>
            <h3 class="text-xl font-bold text-slate-900">{{ number_format($data['total_expenses'], 2) }}</h3>
        </div>
        <div class="bg-white p-4 rounded-xl border shadow-sm border-l-4 border-l-indigo-500">
            <p class="text-[10px] font-bold text-indigo-600 uppercase mb-1">{{ __('Actual Gain') }}</p>
            <h3 class="text-xl font-bold {{ $data['total_net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                {{ number_format($data['total_net'], 2) }}
            </h3>
        </div>
    </div>

    <!-- Trend Charts -->
    <div class="grid grid-cols-1 gap-6 mb-8 no-print">
        <div class="bg-white p-6 rounded-xl border shadow-sm">
            <h3 class="font-bold text-slate-800 mb-4">{{ __('Sales & Profit Trend') }}</h3>
            <div class="h-80" wire:ignore>
                <canvas id="trendChart" wire:key="overview-trend-canvas"></canvas>
            </div>
        </div>
    </div>

    <!-- Monthly Table -->
    <div class="bg-white rounded-xl border shadow-sm overflow-hidden">
        <div class="p-4 border-b bg-slate-50">
            <h3 class="font-bold text-slate-800">{{ __('Monthly Performance Breakdown') }}</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-slate-100 text-slate-600 text-[10px] uppercase font-bold">
                        <th class="px-4 py-3">{{ __('Month') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Sales') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Trend') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('G. Profit') }}</th>
                        <th class="px-4 py-3 text-right text-rose-600">{{ __('Loss') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Adj. G.P') }}</th>
                        <th class="px-4 py-3 text-right text-red-600">{{ __('Expenses') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Gain') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Margin') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @foreach($data['monthly'] as $m)
                    <tr class="hover:bg-slate-50 transition text-xs">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $m['name'] }}</td>
                        <td class="px-4 py-3 text-right text-slate-700">{{ number_format($m['sales'], 2) }}</td>
                        <td class="px-4 py-3 text-right">
                            @if(isset($m['trend']) && $m['trend'] > 0)
                                <span class="text-green-600 font-bold">↑{{ number_format($m['trend'], 0) }}%</span>
                            @elseif(isset($m['trend']) && $m['trend'] < 0)
                                <span class="text-red-600 font-bold">↓{{ number_format(abs($m['trend']), 0) }}%</span>
                            @else
                                <span class="text-slate-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-green-600">{{ number_format($m['profit'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-rose-600 font-medium">{{ number_format($m['inventory_loss'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-indigo-600 font-medium">{{ number_format($m['adjusted_profit'], 2) }}</td>
                        <td class="px-4 py-3 text-right text-red-600">{{ number_format($m['expenses'], 2) }}</td>
                        <td class="px-4 py-3 text-right font-bold {{ $m['net'] >= 0 ? 'text-green-700' : 'text-red-700' }}">
                            {{ number_format($m['net'], 2) }}
                        </td>
                        <td class="px-4 py-3 text-right text-slate-500 text-[10px]">
                            {{ $m['sales'] > 0 ? number_format(($m['profit'] / $m['sales']) * 100, 1) : '0.0' }}%
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="bg-slate-900 text-white font-bold text-xs">
                        <td class="px-4 py-4">{{ __('TOTAL') }}</td>
                        <td class="px-4 py-4 text-right">{{ number_format($data['total_sales'], 2) }}</td>
                        <td class="px-4 py-4 text-right">
                            @php
                                $totalPrevSales = array_sum(array_column($data['monthly'], 'prev_sales'));
                                $totalTrend = $totalPrevSales > 0 ? (($data['total_sales'] - $totalPrevSales) / $totalPrevSales) * 100 : 0;
                            @endphp
                            @if($totalTrend > 0)
                                <span class="text-green-400 text-[10px]">↑{{ number_format($totalTrend, 0) }}%</span>
                            @elseif($totalTrend < 0)
                                <span class="text-red-400 text-[10px]">↓{{ number_format(abs($totalTrend), 0) }}%</span>
                            @endif
                        </td>
                        <td class="px-4 py-4 text-right text-green-400">{{ number_format($data['total_profit'], 2) }}</td>
                        <td class="px-4 py-4 text-right text-rose-400">{{ number_format($data['total_inventory_loss'], 2) }}</td>
                        <td class="px-4 py-4 text-right text-indigo-300">{{ number_format($data['total_adjusted_profit'], 2) }}</td>
                        <td class="px-4 py-4 text-right text-red-400">{{ number_format($data['total_expenses'], 2) }}</td>
                        <td class="px-4 py-4 text-right text-base">{{ number_format($data['total_net'], 2) }}</td>
                        <td class="px-4 py-4 text-right">
                            {{ $data['total_sales'] > 0 ? number_format(($data['total_profit'] / $data['total_sales']) * 100, 1) : '0.0' }}%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; font-size: 10pt; }
            .bg-white { border: none !important; }
            .shadow-sm { box-shadow: none !important; }
            table { border-collapse: collapse !important; width: 100% !important; }
            th, td { border: 1px solid #ddd !important; padding: 8px !important; }
            .bg-slate-900 { background: #333 !important; color: white !important; }
            .text-green-600, .text-green-400, .text-green-700 { color: #008000 !important; }
            .text-red-600, .text-red-400, .text-red-700 { color: #FF0000 !important; }
        }
    </style>

    @script
    <script>
        let trendChart = null;

        function initChart() {
            const ctx = document.getElementById('trendChart');
            if (!ctx) return;

            if (trendChart) {
                trendChart.destroy();
            }

            const data = $wire.monthly;
            const labels = Object.values(data).map(m => m.name.substring(0, 3));
            const salesData = Object.values(data).map(m => m.sales);
            const profitData = Object.values(data).map(m => m.profit);

            trendChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Sales',
                            data: salesData,
                            borderColor: '#6366f1',
                            backgroundColor: 'rgba(99, 102, 241, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Gross Profit',
                            data: profitData,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                usePointStyle: true,
                                padding: 20
                            }
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            padding: 12,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('en-US').format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                borderDash: [2, 2],
                                color: '#e2e8f0'
                            },
                            ticks: {
                                padding: 10,
                                callback: function(value) {
                                    if (value >= 1000) {
                                        return (value / 1000) + 'k';
                                    }
                                    return value;
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                padding: 10
                            }
                        }
                    }
                }
            });
        }

        // Initialize on load
        setTimeout(initChart, 100);

        // Re-initialize when Livewire updates (e.g., year or branch change)
        Livewire.on('updated', () => {
            setTimeout(initChart, 100);
        });
    </script>
    @endscript
</div>
