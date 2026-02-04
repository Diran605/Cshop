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
<body class="bg-slate-100">
    <div class="ui-print-container-sm">
        <div class="no-print mb-4 flex justify-end gap-3">
            <button type="button" onclick="window.print()" class="ui-btn-primary">
                {{ __('Print') }}
            </button>
            <button type="button" onclick="window.close()" class="ui-btn-secondary">
                {{ __('Close') }}
            </button>
        </div>
        @include('sales._receipt', ['sale' => $sale])
    </div>
</body>
</html>
