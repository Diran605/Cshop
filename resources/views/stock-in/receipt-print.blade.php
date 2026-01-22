<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Stock In Receipt') }} - {{ $receipt->receipt_no }}</title>
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
            <button type="button" onclick="window.print()" class="ui-btn-primary">{{ __('Print') }}</button>
            <button type="button" onclick="window.close()" class="ui-btn-secondary">{{ __('Close') }}</button>
        </div>
        @include('stock-in._receipt', ['receipt' => $receipt])
    </div>
</body>
</html>
