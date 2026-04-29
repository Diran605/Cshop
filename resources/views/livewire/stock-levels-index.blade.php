<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Current Stock Levels') }}</h2>
            <div class="ui-page-subtitle">{{ __('View all product stock levels across branches.') }}</div>
        </div>

        @if (session('status'))
            <div class="ui-alert-success">
                {{ session('status') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="ui-card mb-6">
            <div class="ui-card-body">
                <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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
                        <label class="ui-label">{{ __('Source') }}</label>
                        <select wire:model.live="source_filter" class="mt-1 ui-select">
                            <option value="all">{{ __('All Sources') }}</option>
                            <option value="opening_stock">{{ __('Opening Stock') }}</option>
                            <option value="stock_in">{{ __('Stock In') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Product Status') }}</label>
                        <select wire:model.live="status_filter" class="mt-1 ui-select">
                            <option value="all">{{ __('All Statuses') }}</option>
                            <option value="active">{{ __('Active') }}</option>
                            <option value="inactive">{{ __('Inactive') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Stock Status') }}</label>
                        <select wire:model.live="stock_filter" class="mt-1 ui-select">
                            <option value="all">{{ __('All Stock Levels') }}</option>
                            <option value="available">{{ __('In Stock') }}</option>
                            <option value="low">{{ __('Low Stock') }}</option>
                            <option value="out">{{ __('Out of Stock') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Search Product') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="mt-1 ui-input" placeholder="{{ __('Search by product name...') }}" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock Levels Table -->
        <div class="ui-card">
            <div class="ui-card-header">
                <h3 class="ui-card-title">{{ __('Stock Records') }}</h3>
                <div class="text-sm text-slate-500">
                    {{ $stocks->total() }} {{ __('records') }}
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="ui-table">
                    <thead>
                        <tr>
                            <th class="w-10"></th>
                            <th>{{ __('ID') }}</th>
                            @if ($isSuperAdmin)
                                <th>{{ __('Branch') }}</th>
                            @endif
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Current Stock') }}</th>
                            <th>{{ __('Min Stock') }}</th>
                            <th>{{ __('Cost Price (WAC)') }}</th>
                            <th>{{ __('Status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($stocks as $stock)
                            @php
                                $isExpanded = in_array($stock->product_id, $expanded_products);
                            @endphp
                            <tr class="{{ $stock->current_stock <= 0 ? 'bg-red-50' : ($stock->minimum_stock > 0 && $stock->current_stock <= $stock->minimum_stock ? 'bg-amber-50' : '') }}">
                                <td>
                                    <button wire:click="toggleProduct({{ $stock->product_id }})" class="p-1 hover:bg-slate-200 rounded transition-colors">
                                        @if ($isExpanded)
                                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        @else
                                            <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                        @endif
                                    </button>
                                </td>
                                <td class="font-mono text-sm">{{ $stock->id }}</td>
                                @if ($isSuperAdmin)
                                    <td>{{ $stock->branch?->name ?? '-' }}</td>
                                @endif
                                <td>
                                    <div class="font-medium">{{ $stock->product?->name ?? '-' }}</div>
                                    @if ($stock->product?->category)
                                        <div class="text-xs text-slate-500">{{ $stock->product->category->name }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-mono {{ $stock->current_stock <= 0 ? 'text-red-600 font-semibold' : ($stock->minimum_stock > 0 && $stock->current_stock <= $stock->minimum_stock ? 'text-amber-600 font-semibold' : 'text-slate-900') }}">
                                        {{ (int) $stock->current_stock }}
                                    </span>
                                </td>
                                <td>
                                    <span class="font-mono text-slate-600">
                                        {{ (int) ($stock->minimum_stock ?? 0) }}
                                    </span>
                                </td>
                                <td>
                                    @if ($stock->cost_price !== null)
                                        <span class="font-mono text-green-700">
                                            XAF {{ number_format((float) $stock->cost_price, 2) }}
                                        </span>
                                    @else
                                        <span class="text-slate-400">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($stock->product?->status === 'active')
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            {{ __('Active') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            {{ __('Inactive') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>

                            @if ($isExpanded)
                                <tr class="bg-slate-50">
                                    <td colspan="{{ $isSuperAdmin ? 9 : 8 }}" class="p-0">
                                        <div class="p-4 border-l-4 border-slate-300 ml-4 mb-2">
                                            <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2">{{ __('Batch Breakdown') }}</h4>
                                            <table class="w-full text-xs">
                                                <thead>
                                                    <tr class="text-left border-b border-slate-200">
                                                        <th class="py-1">{{ __('Receipt #') }}</th>
                                                        <th class="py-1">{{ __('Date') }}</th>
                                                        <th class="py-1">{{ __('Expiry') }}</th>
                                                        <th class="py-1 text-center">{{ __('Original') }}</th>
                                                        <th class="py-1 text-center">{{ __('Out') }}</th>
                                                        <th class="py-1 text-center">{{ __('Available') }}</th>
                                                        <th class="py-1 text-right">{{ __('Batch Cost') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($stock->batches ?? [] as $batch)
                                                        <tr class="border-b border-slate-100 hover:bg-slate-100">
                                                            <td class="py-1 font-mono text-slate-600">{{ $batch->receipt_no }}</td>
                                                            <td class="py-1 text-slate-600">{{ \Carbon\Carbon::parse($batch->received_at)->format('d/m/Y') }}</td>
                                                            <td class="py-1">
                                                                @if ($batch->expiry_date)
                                                                    <span class="{{ $batch->expiry_date <= now() ? 'text-red-600 font-bold' : ($batch->expiry_date <= now()->addMonths(3) ? 'text-amber-600' : 'text-slate-600') }}">
                                                                        {{ \Carbon\Carbon::parse($batch->expiry_date)->format('d/m/Y') }}
                                                                    </span>
                                                                @else
                                                                    <span class="text-slate-400">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="py-1 text-center font-mono text-slate-600">{{ (int) $batch->quantity }}</td>
                                                            <td class="py-1 text-center font-mono text-slate-400">{{ (int) ($batch->quantity - $batch->remaining_quantity) }}</td>
                                                            <td class="py-1 text-center font-mono font-bold text-slate-900">{{ (int) $batch->remaining_quantity }}</td>
                                                            <td class="py-1 text-right font-mono text-green-700">XAF {{ number_format((float) $batch->cost_price, 2) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="7" class="py-4 text-center text-slate-400 italic">{{ __('No active batches found in stock_in_items.') }}</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 9 : 8 }}" class="text-center py-8 text-slate-500">
                                    {{ __('No stock records found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($stocks->hasPages())
                <div class="ui-card-footer">
                    {{ $stocks->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
