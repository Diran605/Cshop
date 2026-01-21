<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Reports') }}
            </h2>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Branch') }}</label>
                        <select wire:model="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="0">{{ __('Select...') }}</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('From') }}</label>
                        <input type="date" wire:model="date_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('To') }}</label>
                        <input type="date" wire:model="date_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div class="flex items-end">
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="low_stock_only" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                            <span class="ms-2 text-sm text-gray-700">{{ __('Low stock only') }}</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-sm text-gray-500">{{ __('Sales Count') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format((int) $salesCount) }}</div>
                    <div class="mt-2 text-sm text-gray-600">{{ __('Within selected date range') }}</div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="text-sm text-gray-500">{{ __('Sales Total') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-gray-900">{{ number_format((float) $salesTotal, 2) }}</div>
                    <div class="mt-2 text-sm text-gray-600">{{ __('Gross revenue (grand total)') }}</div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Top Products') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
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

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Inventory') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
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
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Sales By Day') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
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

            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900">{{ __('Stock In vs Sales (By Day)') }}</h3>

                    <div class="mt-4 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
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

        <div class="mt-6 bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">{{ __('Per-Product Movement') }}</h3>
                <div class="mt-1 text-sm text-gray-600">{{ __('Totals for selected branch and date range.') }}</div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
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
