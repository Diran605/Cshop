@props(['type' => null, 'itemId' => null])

@php
use App\Models\Alert;

$alertClass = '';
$alertIcon = null;

if ($type && $itemId) {
    $hasAlert = Alert::query()
        ->forUser(auth()->id())
        ->hasAlert($type, $itemId)
        ->exists();

    if ($hasAlert) {
        $alertClass = match($type) {
            'stock_adjustment' => 'bg-soft-yellow',
            'expired_stock' => 'bg-red-50',
            'expiry_warning' => 'bg-soft-yellow',
            'low_stock' => 'bg-soft-blue',
            default => '',
        };

        $alertIcon = match($type) {
            'stock_adjustment' => '⚠️',
            'expired_stock' => '🔴',
            'expiry_warning' => '⚠️',
            'low_stock' => '📉',
            default => null,
        };
    }
}
@endphp

@if ($alertClass)
    <tr class="{{ $alertClass }} border-l-4 border-l-{{ $type === 'expired_stock' ? 'error' : ($type === 'stock_adjustment' || $type === 'expiry_warning' ? 'warning' : 'info') }}">
        {{ $slot }}
    </tr>
@else
    {{ $slot }}
@endif
