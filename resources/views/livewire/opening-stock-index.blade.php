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
                            <th class="text-right">{{ __('Stock') }}</th>
                            <th class="text-right">{{ __('Cost') }}</th>
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
                                <td class="text-right font-mono {{ $stock && $stock->current_stock > 0 ? '' : 'text-red-600' }}">
                                    {{ $stock ? (int) $stock->current_stock : 0 }}
                                </td>
                                <td class="text-right font-mono text-green-700">
                                    @if ($stock && $stock->cost_price !== null)
                                        {{ number_format((float) $stock->cost_price, 0) }}
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td class="text-right font-mono">{{ number_format((float) $product->selling_price, 0) }}</td>
                                <td class="text-center">
                                    @if ($product->status === 'active')
                                        <span class="ui-badge-success">{{ __('Active') }}</span>
                                    @else
                                        <span class="ui-badge-warning">{{ __('Inactive') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" wire:click="editOpeningStock({{ $product->id }})" class="ui-btn-link text-xs">
                                        {{ __('Edit') }}
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-8 text-slate-500">
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

    <!-- Edit Opening Stock Modal -->
    @if ($show_edit_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeEditModal" data-modal-overlay></div>
            <div class="relative w-full max-w-lg ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="text-sm text-slate-500">{{ __('Edit Opening Stock') }}</div>
                    <div class="mt-1 font-semibold text-slate-900">{{ $editing_product_name }}</div>
                </div>

                <div class="p-4 space-y-4">
                    <div>
                        <label class="ui-label">{{ __('Opening Quantity') }}</label>
                        <input type="number" wire:model.defer="opening_quantity" class="mt-1 ui-input" min="0" />
                        @error('opening_quantity') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Cost Price (optional)') }}</label>
                        <input type="number" wire:model.defer="opening_cost_price" class="mt-1 ui-input" min="0" step="0.01" placeholder="XAF" />
                        @error('opening_cost_price') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Expiry Date (optional)') }}</label>
                        <input type="date" wire:model.defer="opening_expiry_date" class="mt-1 ui-input" />
                        @error('opening_expiry_date') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div class="text-sm text-blue-800">
                                {{ __('Changing the stock quantity will create a stock movement record and adjust the current stock level accordingly.') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                    <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" wire:click="saveOpeningStock" class="ui-btn-primary">
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
