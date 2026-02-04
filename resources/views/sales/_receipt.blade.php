<div class="ui-card">
    <div class="ui-card-body">
        <div class="text-center">
            <div class="text-xl font-bold text-slate-900">{{ config('app.name') }}</div>
            <div class="mt-1 text-sm text-slate-600">{{ $sale->branch?->name ?? '-' }}</div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-4 text-sm">
            <div>
                <div class="text-slate-500">{{ __('Receipt No') }}</div>
                <div class="font-medium text-slate-900">{{ $sale->receipt_no }}</div>
            </div>
            <div>
                <div class="text-slate-500">{{ __('Date') }}</div>
                <div class="font-medium text-slate-900">{{ $sale->sold_at?->format('Y-m-d H:i') }}</div>
            </div>
            <div>
                <div class="text-slate-500">{{ __('Cashier') }}</div>
                <div class="font-medium text-slate-900">{{ $sale->user?->name ?? '-' }}</div>
            </div>
            <div>
                <div class="text-slate-500">{{ __('Payment') }}</div>
                <div class="font-medium text-slate-900">{{ strtoupper($sale->payment_method) }}</div>
            </div>
        </div>

        <div class="mt-6 overflow-x-auto">
            <div class="ui-table-wrap">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th>{{ __('Item') }}</th>
                            <th>{{ __('Qty') }}</th>
                            <th>{{ __('Price') }}</th>
                            <th>{{ __('Total') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sale->items as $item)
                            <tr>
                                <td class="text-slate-900">{{ $item->product?->name ?? '-' }}</td>
                                <td>
                                    @if ((string) $item->entry_mode === 'bulk')
                                        {{ (int) ($item->bulk_quantity ?? 0) }} {{ __('bulk') }}
                                        <span class="text-xs text-slate-500">({{ (int) $item->quantity }} {{ __('units') }})</span>
                                    @else
                                        {{ (int) $item->quantity }}
                                    @endif
                                </td>
                                <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                                <td>{{ number_format((float) $item->line_total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6 border-t border-slate-200 pt-4 text-sm">
            <div class="flex items-center justify-between">
                <div class="text-slate-600">{{ __('Grand Total') }}</div>
                <div class="font-semibold text-slate-900">{{ number_format((float) $sale->grand_total, 2) }}</div>
            </div>
            <div class="mt-1 flex items-center justify-between">
                <div class="text-slate-600">{{ __('COGS') }}</div>
                <div class="font-medium text-slate-900">{{ number_format((float) $sale->cogs_total, 2) }}</div>
            </div>
            <div class="mt-1 flex items-center justify-between">
                <div class="text-slate-600">{{ __('Profit') }}</div>
                <div class="font-medium text-slate-900">{{ number_format((float) $sale->profit_total, 2) }}</div>
            </div>
            <div class="mt-1 flex items-center justify-between">
                <div class="text-slate-600">{{ __('Amount Paid') }}</div>
                <div class="font-medium text-slate-900">{{ number_format((float) $sale->amount_paid, 2) }}</div>
            </div>
            <div class="mt-1 flex items-center justify-between">
                <div class="text-slate-600">{{ __('Change Due') }}</div>
                <div class="font-medium text-slate-900">{{ number_format((float) $sale->change_due, 2) }}</div>
            </div>
        </div>

        @if ($sale->notes)
            <div class="mt-6 text-sm text-slate-700">
                <div class="text-slate-500">{{ __('Notes') }}</div>
                <div class="mt-1">{{ $sale->notes }}</div>
            </div>
        @endif

        <div class="mt-8 text-center text-xs text-slate-500">
            {{ __('Thank you!') }}
        </div>
    </div>
</div>
