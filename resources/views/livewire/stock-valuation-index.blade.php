<div class="ui-page">
    <div class="ui-page-container print-container">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="ui-page-title">{{ __('Stock Valuation') }}</h2>
                <div class="ui-page-subtitle">{{ __('View stock quantities and cost prices across opening stock and stock-in') }}</div>
            </div>
            <div class="flex items-center gap-3 no-print">
                <div class="ui-tabs">
                    <a href="{{ route('reports.index') }}" class="ui-tab">{{ __('Sales') }}</a>
                    <a href="{{ route('reports.profit') }}" class="ui-tab">{{ __('Profit') }}</a>
                    <a href="{{ route('reports.stock') }}" class="ui-tab">{{ __('Stock') }}</a>
                    <a href="{{ route('reports.expenses') }}" class="ui-tab">{{ __('Expenses') }}</a>
                    <a href="{{ route('reports.expiry') }}" class="ui-tab">{{ __('Expiry') }}</a>
                    <a href="{{ route('clearance.reports') }}" class="ui-tab">{{ __('Clearance') }}</a>
                    <a href="{{ route('daily_summary.index') }}" class="ui-tab">{{ __('Summary') }}</a>
                    <a href="{{ route('stock_valuation.index') }}" class="ui-tab ui-tab-active">{{ __('Valuation') }}</a>
                </div>
                <button onclick="window.print()" class="ui-btn-primary gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" /></svg>
                    {{ __('Print') }}
                </button>
            </div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="ui-kpi-card">
                <div class="ui-kpi-title">{{ __('Total Products') }}</div>
                <div class="ui-kpi-value mt-1">{{ number_format($summary['total_products']) }}</div>
            </div>
            <div class="ui-kpi-card">
                <div class="ui-kpi-title">{{ __('Total Quantity') }}</div>
                <div class="ui-kpi-value mt-1">{{ number_format($summary['total_quantity']) }}</div>
            </div>
            <div class="ui-kpi-card">
                <div class="ui-kpi-title">{{ __('Total Stock Value') }}</div>
                <div class="ui-kpi-value mt-1 text-green-600">XAF {{ number_format($summary['total_value'], 2) }}</div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="ui-card mb-6 no-print">
            <div class="ui-card-body">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @if ($isSuperAdmin)
                        <div>
                            <label class="ui-label">{{ __('Branch') }}</label>
                            <select wire:model.live="branch_id" class="mt-1 ui-select">
                                <option value="0">{{ __('All Branches') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="ui-label">{{ __('Category') }}</label>
                        <select wire:model.live="category_filter" class="mt-1 ui-select">
                            <option value="">{{ __('All Categories') }}</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="{{ $isSuperAdmin ? '' : 'md:col-span-2' }}">
                        <label class="ui-label">{{ __('Search Product') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="{{ __('Product name...') }}" class="mt-1 ui-input" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="ui-card">
            <div class="ui-card-body p-0">
                <div class="overflow-x-auto">
                    <table class="ui-table min-w-full">
                        <thead>
                            <tr>
                                @if ($isSuperAdmin)
                                    <th class="whitespace-nowrap">{{ __('Branch') }}</th>
                                @endif
                                <th class="whitespace-nowrap">{{ __('Product') }}</th>
                                <th class="whitespace-nowrap">{{ __('Category') }}</th>
                                <th class="whitespace-nowrap text-center">{{ __('Qty') }}</th>
                                <th class="whitespace-nowrap text-right">{{ __('Actual Cost (Avg)') }}</th>
                                <th class="whitespace-nowrap text-right">{{ __('Sell Price') }}</th>
                                <th class="whitespace-nowrap text-right">{{ __('Stock Value') }}</th>
                                <th class="whitespace-nowrap text-right">{{ __('Margin') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($products as $product)
                                <tr wire:key="product-{{ $product->id }}">
                                    @if ($isSuperAdmin)
                                        <td class="whitespace-nowrap">{{ $product->branch?->name ?? '-' }}</td>
                                    @endif
                                    <td class="whitespace-nowrap">
                                        <div class="font-medium text-slate-900">{{ $product->name }}</div>
                                    </td>
                                    <td class="whitespace-nowrap text-slate-600">{{ $product->category?->name ?? '-' }}</td>
                                    <td class="whitespace-nowrap text-center font-mono">
                                        @php $qty = $product->batch_total_qty; @endphp
                                        <span class="{{ $qty <= 0 ? 'text-red-600 font-bold' : 'text-slate-900' }}">
                                            {{ number_format($qty) }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap text-right font-mono font-semibold text-slate-900">
                                        XAF {{ number_format((float) $product->actual_cost_price, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap text-right font-mono font-semibold text-green-600">
                                        XAF {{ number_format((float) $product->selling_price, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap text-right font-mono font-bold text-blue-600">
                                        XAF {{ number_format($product->batch_total_value, 2) }}
                                    </td>
                                    <td class="whitespace-nowrap text-right">
                                        @php
                                            $currentCost = (float) $product->actual_cost_price;
                                            $margin = $currentCost > 0 ? (($product->selling_price - $currentCost) / $currentCost) * 100 : null;
                                        @endphp
                                        @if ($margin !== null)
                                            <span class="{{ $margin >= 0 ? 'text-green-600' : 'text-red-600' }} font-mono font-medium">
                                                {{ $margin >= 0 ? '+' : '' }}{{ number_format($margin, 1) }}%
                                            </span>
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isSuperAdmin ? 8 : 7 }}" class="ui-table-empty">
                                        {{ __('No products found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($products->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
