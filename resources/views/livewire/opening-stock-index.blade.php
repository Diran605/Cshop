<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Manage Opening Stock') }}</h2>
            <div class="ui-page-subtitle">{{ __('Set initial stock quantities for products.') }}</div>
        </div>

        @if (session('status'))
            <div class="ui-alert-success">
                {{ session('status') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="ui-card mb-6">
            <div class="ui-card-body">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @if ($isSuperAdmin)
                        <div>
                            <label class="ui-label">{{ __('Branch') }}</label>
                            <select wire:model.live="branch_id" class="mt-1 ui-select">
                                <option value="0">{{ __('Select Branch') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="ui-label">{{ __('Product Status') }}</label>
                        <select wire:model.live="status_filter" class="mt-1 ui-select">
                            <option value="all">{{ __('All Statuses') }}</option>
                            <option value="active">{{ __('Active') }}</option>
                            <option value="inactive">{{ __('Inactive') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Search Product') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="mt-1 ui-input" placeholder="{{ __('Search by product name...') }}" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Products Table -->
        <div class="ui-card">
            <div class="ui-card-header">
                <h3 class="ui-card-title">{{ __('Products') }}</h3>
                <div class="text-sm text-slate-500">
                    {{ $products->total() }} {{ __('products') }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="ui-table text-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th class="text-right">{{ __('Opening Stock') }}</th>
                            <th class="text-right">{{ __('Current Stock') }}</th>
                            <th class="text-right">{{ __('Opening Cost') }}</th>
                            <th class="text-right">{{ __('Price') }}</th>
                            <th class="text-center">{{ __('Status') }}</th>
                            <th class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            @php
                                $stock = $product->stocks->firstWhere('branch_id', $branch_id);
                            @endphp
                            <tr>
                                <td class="font-medium max-w-[180px] truncate">{{ $product->name }}</td>
                                <td class="whitespace-nowrap">{{ $product->category?->name ?? '-' }}</td>
                                <td class="text-right font-mono text-blue-700 font-bold">
                                    {{ (int) $product->actual_opening_qty }}
                                </td>
                                <td class="text-right font-mono {{ $stock && $stock->current_stock > 0 ? 'text-slate-900' : 'text-red-600' }}">
                                    {{ $stock ? (int) $stock->current_stock : 0 }}
                                </td>
                                <td class="text-right font-mono text-slate-600">
                                    @if ($product->actual_opening_cost !== null)
                                        {{ number_format((float) $product->actual_opening_cost, 2) }}
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="text-right font-mono">{{ number_format((float) $product->selling_price, 2) }}</td>
                                <td class="text-center">
                                    @if ($product->status === 'active')
                                        <span class="ui-badge-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="ui-badge-warning">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" wire:click="viewOpeningStock({{ $product->id }})" class="ui-btn-secondary text-xs">
                                        {{ __('View') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 text-slate-500">
                                    {{ __('No products found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($products->hasPages())
                <div class="ui-card-footer">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- View Opening Stock Modal -->
    @if ($show_edit_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeViewModal" data-modal-overlay></div>
            <div class="relative w-full max-w-lg ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="text-sm text-slate-500">{{ __('Opening Stock Details') }}</div>
                    <div class="mt-1 font-semibold text-slate-900">{{ $editing_product_name }}</div>
                </div>

                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('Current Stock') }}</label>
                            <div class="mt-1 text-lg font-semibold text-slate-900 font-mono">{{ number_format($opening_quantity) }}</div>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-500 uppercase tracking-wide">{{ __('Cost Price') }}</label>
                            <div class="mt-1 text-lg font-semibold text-slate-900 font-mono">
                                {{ $opening_cost_price ? number_format((float) $opening_cost_price, 2) : '-' }}
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-lg p-3">
                        <div class="text-sm text-slate-600">
                            {{ __('Opening stock values are fixed at system initialization. To adjust current inventory, please use the Stock Adjustments or Manage Products module.') }}
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-end">
                    <button type="button" wire:click="closeViewModal" class="ui-btn-secondary" data-modal-close>
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
