<div class="bg-white shadow-sm rounded-lg p-6">
    <div class="text-center">
        <div class="text-xl font-bold text-gray-900">{{ config('app.name') }}</div>
        <div class="mt-1 text-sm text-gray-600">{{ $receipt->branch?->name ?? '-' }}</div>
    </div>

    <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
        <div>
            <div class="text-gray-500">{{ __('Receipt No') }}</div>
            <div class="font-medium text-gray-900">{{ $receipt->receipt_no }}</div>
        </div>
        <div>
            <div class="text-gray-500">{{ __('Date') }}</div>
            <div class="font-medium text-gray-900">{{ $receipt->received_at?->format('Y-m-d H:i') }}</div>
        </div>
        <div>
            <div class="text-gray-500">{{ __('User') }}</div>
            <div class="font-medium text-gray-900">{{ $receipt->user?->name ?? '-' }}</div>
        </div>
        <div>
            <div class="text-gray-500">{{ __('Total Qty') }}</div>
            <div class="font-medium text-gray-900">{{ (int) $receipt->total_quantity }}</div>
        </div>
    </div>

    @if ($receipt->notes)
        <div class="mt-4 text-sm text-gray-700">{{ $receipt->notes }}</div>
    @endif

    <div class="mt-6 overflow-x-auto">
        <table class="ui-table">
            <thead>
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Qty') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Cost') }}</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Total') }}</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($receipt->items as $item)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $item->product?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            @if ((string) $item->entry_mode === 'bulk')
                                {{ (int) ($item->bulk_quantity ?? 0) }} {{ __('bulk') }}
                                <span class="text-xs text-gray-500">({{ (int) $item->quantity }} {{ __('units') }})</span>
                            @else
                                {{ (int) $item->quantity }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $item->cost_price !== null ? number_format((float) $item->cost_price, 2) : '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-700">
                            {{ $item->line_total !== null ? number_format((float) $item->line_total, 2) : '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4 flex items-center justify-end text-sm">
        <div class="text-gray-500">{{ __('Total Cost') }}:</div>
        <div class="ms-2 font-semibold text-gray-900">
            {{ $receipt->total_cost !== null ? number_format((float) $receipt->total_cost, 2) : '-' }}
        </div>
    </div>
</div>
