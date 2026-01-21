<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Sales Receipt') }} - {{ $sale->receipt_no }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-2xl mx-auto py-8 px-4">
        <div class="no-print mb-4 flex justify-end gap-3">
            <button type="button" onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm text-white hover:bg-indigo-700">
                {{ __('Print') }}
            </button>
            <button type="button" onclick="window.close()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 bg-white hover:bg-gray-50">
                {{ __('Close') }}
            </button>
        </div>

        <div class="bg-white shadow-sm rounded-lg p-6">
            <div class="text-center">
                <div class="text-xl font-bold text-gray-900">{{ config('app.name') }}</div>
                <div class="mt-1 text-sm text-gray-600">{{ $sale->branch?->name ?? '-' }}</div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-gray-500">{{ __('Receipt No') }}</div>
                    <div class="font-medium text-gray-900">{{ $sale->receipt_no }}</div>
                </div>
                <div>
                    <div class="text-gray-500">{{ __('Date') }}</div>
                    <div class="font-medium text-gray-900">{{ $sale->sold_at?->format('Y-m-d H:i') }}</div>
                </div>
                <div>
                    <div class="text-gray-500">{{ __('Cashier') }}</div>
                    <div class="font-medium text-gray-900">{{ $sale->user?->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">{{ __('Payment') }}</div>
                    <div class="font-medium text-gray-900">{{ strtoupper($sale->payment_method) }}</div>
                </div>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Item') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Qty') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Price') }}</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($sale->items as $item)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item->product?->name ?? '-' }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6 border-t border-gray-200 pt-4 text-sm">
                <div class="flex items-center justify-between">
                    <div class="text-gray-600">{{ __('Grand Total') }}</div>
                    <div class="font-semibold text-gray-900">{{ number_format((float) $sale->grand_total, 2) }}</div>
                </div>
                <div class="mt-1 flex items-center justify-between">
                    <div class="text-gray-600">{{ __('Amount Paid') }}</div>
                    <div class="font-medium text-gray-900">{{ number_format((float) $sale->amount_paid, 2) }}</div>
                </div>
                <div class="mt-1 flex items-center justify-between">
                    <div class="text-gray-600">{{ __('Change Due') }}</div>
                    <div class="font-medium text-gray-900">{{ number_format((float) $sale->change_due, 2) }}</div>
                </div>
            </div>

            @if ($sale->notes)
                <div class="mt-6 text-sm text-gray-700">
                    <div class="text-gray-500">{{ __('Notes') }}</div>
                    <div class="mt-1">{{ $sale->notes }}</div>
                </div>
            @endif

            <div class="mt-8 text-center text-xs text-gray-500">
                {{ __('Thank you!') }}
            </div>
        </div>
    </div>
</body>
</html>
