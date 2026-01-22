<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Stock In Receipts') }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            .print-page { break-after: page; }
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="no-print mb-4 flex justify-end gap-3">
            <button type="button" onclick="window.print()" class="ui-btn-primary">{{ __('Print') }}</button>
            <button type="button" onclick="window.close()" class="ui-btn-secondary">{{ __('Close') }}</button>
        </div>

        @forelse ($receipts as $receipt)
            <div class="print-page">
                @include('stock-in._receipt', ['receipt' => $receipt])
            </div>
        @empty
            <div class="bg-white shadow-sm rounded-lg p-6 text-sm text-gray-700">
                {{ __('No receipts selected.') }}
            </div>
        @endforelse
    </div>
</body>
</html>
