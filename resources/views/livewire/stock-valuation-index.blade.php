<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Stock Valuation') }}</h2>
            <div class="ui-page-subtitle">{{ __('View stock quantities and cost prices across opening stock and stock-in') }}</div>
        </div>

        {{-- Summary Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-slate-500">{{ __('Total Products') }}</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['total_products']) }}</div>
                </div>
            </div>
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-slate-500">{{ __('Total Quantity') }}</div>
                    <div class="mt-1 text-2xl font-bold text-slate-900">{{ number_format($summary['total_quantity']) }}</div>
                </div>
            </div>
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-slate-500">{{ __('Total Stock Value') }}</div>
                    <div class="mt-1 text-2xl font-bold text-green-600">{{ number_format($summary['total_value'], 2) }}</div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="ui-card mb-6">
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
            <div class="ui-card-body">
                <div class="overflow-x-auto">
                    <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    @if ($isSuperAdmin)
                                        <th>{{ __('Branch') }}</th>
                                    @endif
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th class="text-center">{{ __('Current Qty') }}</th>
                                    <th class="text-right">{{ __('Opening Cost') }}</th>
                                    <th class="text-right">{{ __('Stock-In Cost') }}</th>
                                    <th class="text-right">{{ __('Current Cost') }}</th>
                                    <th class="text-right">{{ __('Selling Price') }}</th>
                                    <th class="text-right">{{ __('Stock Value') }}</th>
                                    <th class="text-right">{{ __('Margin') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr wire:key="product-{{ $product->id }}">
                                        @if ($isSuperAdmin)
                                            <td class="whitespace-nowrap">{{ $product->branch?->name ?? '-' }}</td>
                                        @endif
                                        <td class="whitespace-nowrap">
                                            <div class="font-medium">{{ $product->name }}</div>
                                        </td>
                                        <td class="whitespace-nowrap">{{ $product->category?->name ?? '-' }}</td>
                                        <td class="text-center font-mono">
                                            <span class="{{ ($product->stock?->current_stock ?? 0) <= 0 ? 'text-red-600' : '' }}">
                                                {{ $product->stock?->current_stock ?? 0 }}
                                            </span>
                                        </td>
                                        <td class="text-right font-mono">
                                            {{ $product->opening_cost_price !== null ? number_format((float) $product->opening_cost_price, 2) : '-' }}
                                        </td>
                                        <td class="text-right font-mono">
                                            {{ $product->stock_in_cost_price !== null ? number_format((float) $product->stock_in_cost_price, 2) : '-' }}
                                        </td>
                                        <td class="text-right font-mono font-medium">
                                            {{ $product->stock?->cost_price !== null ? number_format((float) $product->stock->cost_price, 2) : '-' }}
                                        </td>
                                        <td class="text-right font-mono font-medium text-green-600">
                                            {{ number_format((float) $product->selling_price, 2) }}
                                        </td>
                                        <td class="text-right font-mono font-bold text-blue-600">
                                            @php
                                                $stockValue = ($product->stock?->current_stock ?? 0) * (float) ($product->stock?->cost_price ?? 0);
                                            @endphp
                                            {{ number_format($stockValue, 2) }}
                                        </td>
                                        <td class="text-right">
                                            @php
                                                $currentCost = (float) ($product->stock?->cost_price ?? 0);
                                                $margin = $currentCost > 0 ? (($product->selling_price - $currentCost) / $currentCost) * 100 : null;
                                            @endphp
                                            @if ($margin !== null)
                                                <span class="{{ $margin >= 0 ? 'text-green-600' : 'text-red-600' }} font-mono">
                                                    {{ $margin >= 0 ? '+' : '' }}{{ number_format($margin, 1) }}%
                                                </span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $isSuperAdmin ? 10 : 9 }}" class="ui-table-empty">
                                            {{ __('No products found.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                @if ($products->hasPages())
                    <div class="mt-4 ui-card-footer">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
