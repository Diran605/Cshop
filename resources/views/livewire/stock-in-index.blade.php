<div class="ui-page">
    <div class="ui-page-container">
        {{-- Header --}}
        <div class="mb-6">
            <div>
                <div class="text-xs font-bold tracking-wider uppercase text-purple-600 mb-1">Inventory Management</div>
                <h1 class="text-2xl font-bold text-slate-900">Record Stock In</h1>
            </div>
        </div>

        @if (session('status'))
            <div class="ui-alert-success">{{ session('status') }}</div>
        @endif
        @if (session('warning'))
            <div class="ui-alert-warning">{{ session('warning') }}</div>
        @endif
        @if (session('error'))
            <div class="ui-alert-danger">{{ session('error') }}</div>
        @endif

        <div class="space-y-6">
            {{-- Top Section: Stock Configuration --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="text-sm font-medium text-slate-700 mb-4">{{ __('Stock Configuration') }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="ui-label">{{ __('Received Date') }}</label>
                            <input type="date" wire:model.defer="received_at_date" class="mt-1 ui-input" />
                            @error('received_at_date') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                        @if ($isSuperAdmin)
                        <div>
                            <label class="ui-label">{{ __('Branch') }}</label>
                            <select wire:model.live="branch_id" class="mt-1 ui-select">
                                <option value="0">{{ __('Select...') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                            @error('branch_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                        @else
                        <div>
                            <label class="ui-label">{{ __('Branch') }}</label>
                            <div class="rounded-lg border border-slate-300/80 bg-white/60 px-3 py-2 text-sm text-slate-700 mt-1">
                                {{ $branches->first()?->name ?? '-' }}
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Main Items Area --}}
            <div class="ui-card">
                <div class="ui-card-body space-y-6">
                    <!-- Add Product -->
                    <div class="bg-slate-50 rounded-lg p-4">
                        <div class="text-sm font-medium text-slate-700 mb-3">{{ __('Add Product') }}</div>
                        <div class="grid grid-cols-1 md:grid-cols-12 gap-3">
                            <div class="md:col-span-12">
                                <label class="ui-label mb-1">{{ __('Product') }}</label>
                                @if (!$selectedProduct)
                                <div class="flex gap-2 relative">
                                    <div class="w-1/3">
                                        <select wire:model.live="category_id" class="ui-select h-[42px]">
                                            <option value="">{{ __('All Categories') }}</option>
                                            @if(isset($categories))
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <div x-data="{ open: false }" @click.away="open = false" class="w-2/3 relative">
                                        <input type="text" wire:model.live.debounce.300ms="product_search" @focus="open = true" @keydown="open = true" class="ui-input h-[42px] w-full" placeholder="Search product..." autocomplete="off" />
                                        @if (count($searchableProducts) > 0)
                                            <div x-show="open" x-transition style="display: none;" class="border border-slate-300 rounded-md max-h-60 overflow-y-auto absolute z-50 bg-white w-full shadow-xl mt-1">
                                                @foreach ($searchableProducts as $product)
                                                    <button type="button" @click="open = false" wire:click="selectProduct({{ $product->id }})" class="w-full text-left px-3 py-2 hover:bg-slate-100 border-b border-slate-100 last:border-b-0">
                                                        <div class="font-medium">{{ $product->name }}</div>
                                                        <div class="text-xs text-slate-500">{{ $product->category?->name ?? 'Uncategorized' }}</div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endif
                                
                                @if ($selectedProduct)
                                <div class="p-2 bg-green-50 border border-green-200 rounded-md h-[42px] flex items-center justify-between">
                                    <div class="truncate">
                                        <span class="font-medium text-green-800">{{ $selectedProduct->name }}</span>
                                        <span class="text-xs text-green-600 ml-2">{{ $selectedProduct->category?->name }}</span>
                                    </div>
                                    <button type="button" wire:click="$set('product_id', 0); $set('selectedProduct', null)" class="text-green-700 hover:text-green-900 text-sm font-semibold">Clear</button>
                                </div>
                                @endif
                                @if ($isSuperAdmin && $branch_id <= 0)
                                    <div class="mt-1 text-xs text-slate-500">{{ __('Select a branch first to load products.') }}</div>
                                @endif
                            </div>

                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Mode') }}</label>
                                <select wire:model.live="entry_mode" class="mt-1 ui-select" @if (!($selectedProduct && $selectedProduct->bulk_enabled)) disabled @endif>
                                    <option value="unit">{{ __('Unit') }}</option>
                                    <option value="bulk">{{ __('Bulk') }}</option>
                                </select>
                                @if ($entry_mode === 'bulk' && $selectedProduct && $selectedProduct->bulk_enabled)
                                <div class="text-xs text-green-600 mt-1 truncate">
                                    {{ $selectedProduct->bulkType?->units_per_bulk ?? 0 }} units/bulk
                                </div>
                                @endif
                            </div>
                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Qty') }}</label>
                                @if ($entry_mode === 'bulk' && $selectedProduct && $selectedProduct->bulk_enabled)
                                <input type="number" wire:model.defer="bulk_quantity" min="1" class="mt-1 ui-input text-center px-1" />
                                @else
                                <input type="number" wire:model.defer="quantity" min="1" class="mt-1 ui-input text-center px-1" />
                                @endif
                            </div>
                            <div class="md:col-span-3">
                                <label class="ui-label">
                                    @if ($entry_mode === 'bulk' && $selectedProduct && $selectedProduct->bulk_enabled)
                                    {{ __('Cost per Bulk') }}
                                    @else
                                    {{ __('Cost per Unit') }}
                                    @endif
                                </label>
                                <input type="number" min="0" step="0.01" class="mt-1 ui-input" wire:model.defer="cost_price" placeholder="0.00" />
                                @error('cost_price') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Expiry (opt)') }}</label>
                                <input type="date" wire:model.defer="expiry_date" class="mt-1 ui-input" />
                            </div>
                            <div class="md:col-span-3 flex items-start mt-[28px]">
                                <button type="button" wire:click="addDraftLine" class="w-full ui-btn-primary h-[42px]">
                                    {{ __('Add Line') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Items -->
                    <div>
                        <div class="text-sm font-medium text-slate-700 mb-2">{{ __('Draft Lines') }}</div>
                        @php $draftItems = array_values($draft_lines); @endphp
                        @if (count($draftItems) > 0)
                            <div class="overflow-x-auto">
                                <table class="ui-table text-sm">
                                    <thead>
                                        <tr>
                                            <th class="text-center">{{ __('Product') }}</th>
                                            <th class="text-center">{{ __('Qty') }}</th>
                                            <th class="text-center">{{ __('Cost') }}</th>
                                            <th class="text-center">{{ __('Total') }}</th>
                                            <th class="text-center"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($draftItems as $line)
                                            @php
                                                $isBulk = (string) ($line['entry_mode'] ?? 'unit') === 'bulk';
                                                $displayQty = $isBulk ? (int) ($line['bulk_quantity'] ?? 0) : (int) ($line['quantity'] ?? 0);
                                                $lineTotal = (($line['cost_price'] ?? null) !== null && ($line['cost_price'] ?? '') !== '') ? ((float) $line['cost_price'] * (int) ($line['quantity'] ?? 0)) : null;
                                            @endphp
                                            <tr wire:key="draft-line-{{ $line['key'] }}">
                                                <td class="text-center">
                                                    <div class="font-medium text-slate-900">{{ $line['name'] ?? '-' }}</div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="flex flex-col items-center justify-center">
                                                        <input type="number" min="1" 
                                                            value="{{ $displayQty }}" 
                                                            wire:change="setDraftQuantity({{ $line['key'] }}, $event.target.value)" 
                                                            class="ui-input text-center px-1 py-1 h-8 w-20 font-medium bg-white" />
                                                        @if($isBulk) <div class="text-xs text-slate-500 mt-1">bulk ({{ (int) ($line['quantity'] ?? 0) }} units)</div> @endif
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <div class="flex flex-col items-center justify-center">
                                                        @php $displayCost = $isBulk ? ((float)($line['cost_price'] ?? 0) * (int)($line['units_per_bulk'] ?? 1)) : ((float)($line['cost_price'] ?? 0)); @endphp
                                                        <input type="number" min="0" step="0.01" 
                                                            value="{{ ($line['cost_price'] ?? null) !== null ? number_format($displayCost, 2, '.', '') : '' }}" 
                                                            wire:change="setDraftCost({{ $line['key'] }}, $event.target.value)" 
                                                            class="ui-input text-center px-1 py-1 h-8 w-24 font-mono bg-white" placeholder="0.00" />
                                                    </div>
                                                </td>
                                                <td class="text-center font-mono font-semibold text-purple-700">
                                                    @if ($lineTotal !== null)
                                                        {{ number_format((float) $lineTotal, 2) }}
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" wire:click="removeDraftLine({{ (int) $line['key'] }})" class="text-red-500 hover:text-red-700">
                                                        <svg class="w-5 h-5 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        @php
                                            $draftTotalQty = collect($draftItems)->sum('quantity');
                                            $draftTotalCost = collect($draftItems)->reduce(function ($carry, $row) { $cp = $row['cost_price'] ?? null; $qty = (int) ($row['quantity'] ?? 0); return $carry + (($cp !== null && $cp !== '') ? ((float) $cp * $qty) : 0); }, 0);
                                        @endphp
                                        <tr>
                                            <td class="text-right font-medium text-slate-700">{{ __('Totals') }}</td>
                                            <td class="text-center font-medium">{{ (int) $draftTotalQty }}</td>
                                            <td></td>
                                            <td class="text-center font-mono font-bold text-purple-700">{{ number_format((float) $draftTotalCost, 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8 bg-slate-50 border border-slate-200 border-dashed rounded-lg text-slate-500">
                                {{ __('No products added yet.') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Checkout Details / Notes --}}
            <div class="ui-card">
                <div class="ui-card-body space-y-4">
                    <h3 class="text-sm font-medium text-slate-700">{{ __('Finalize Receipt') }}</h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="ui-label">{{ __('Supplier (opt)') }}</label>
                            <input type="text" wire:model.defer="supplier_name" class="mt-1 ui-input" placeholder="Supplier name..." />
                            @error('supplier_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="ui-label">{{ __('Batch Ref (opt)') }}</label>
                            <input type="text" wire:model.defer="batch_ref_no" class="mt-1 ui-input" placeholder="Batch reference..." />
                            @error('batch_ref_no') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Notes (optional)') }}</label>
                        <textarea wire:model.defer="notes" rows="2" class="mt-1 ui-input" placeholder="Add any notes..."></textarea>
                        @error('notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-slate-200">
                        <button type="button" wire:click="clearDraftLines" class="ui-btn-secondary h-12 px-8" @if(count($draftItems) === 0) disabled @endif>
                            {{ __('Clear Lines') }}
                        </button>
                        <button type="button" wire:click="save" class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-3 px-12 rounded-lg shadow-lg transition-all h-12" @if(count($draftItems) === 0) disabled @endif>
                            {{ __('Post Receipt') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        @if ($selectedReceipt)
            <div wire:key="receipt-modal-{{ $selectedReceipt->id }}" class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="selectReceipt(0)" data-modal-overlay></div>
                <div class="relative w-full max-w-3xl ui-card">
                    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Receipt Posted') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ $selectedReceipt->receipt_no }}</div>
                            <div class="mt-1 text-sm text-slate-600">
                                {{ $selectedReceipt->branch?->name ?? '-' }}
                                @if ($selectedReceipt->user)
                                    {{ '• ' . $selectedReceipt->user->name }}
                                @endif
                                {{ '• ' . $selectedReceipt->received_at?->format('Y-m-d H:i') }}
                            </div>
                            @php
                                $firstItem = $selectedReceipt->items->first();
                            @endphp
                            @if ($firstItem && ($firstItem->supplier_name || $firstItem->batch_ref_no))
                                <div class="mt-2 text-xs font-medium text-slate-500 bg-slate-100 inline-flex px-2 py-1 rounded">
                                    @if($firstItem->supplier_name) Supplier: <span class="text-slate-800 ml-1 mr-3">{{ $firstItem->supplier_name }}</span> @endif
                                    @if($firstItem->batch_ref_no) Batch: <span class="text-slate-800 ml-1">{{ $firstItem->batch_ref_no }}</span> @endif
                                </div>
                            @endif
                        </div>
                        <button type="button" wire:click="selectReceipt(0)" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                    </div>
                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div></div>
                            <a href="{{ route('stock_in.print', $selectedReceipt->id) }}" target="_blank" class="ui-btn-secondary">
                                {{ __('Print Receipt') }}
                            </a>
                        </div>
                        @if ($selectedReceipt->notes)
                            <div class="mt-4 text-sm text-slate-700">{{ $selectedReceipt->notes }}</div>
                        @endif
                        <div class="mt-4 overflow-x-auto">
                            <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Qty') }}</th>
                                        <th>{{ __('Expiry') }}</th>
                                        <th>{{ __('Cost') }}</th>
                                        <th>{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedReceipt->items as $item)
                                        <tr wire:key="receipt-modal-item-{{ $item->id }}">
                                            <td class="text-slate-900">{{ $item->product?->name ?? '-' }}</td>
                                            <td>
                                                @if ((string) $item->entry_mode === 'bulk')
                                                    {{ (int) ($item->bulk_quantity ?? 0) }} {{ __('bulk') }}
                                                    <span class="text-xs text-slate-500">({{ (int) $item->quantity }} {{ __('units') }})</span>
                                                @else
                                                    {{ (int) $item->quantity }}
                                                @endif
                                            </td>
                                            <td>
                                                {{ $item->expiry_date ? $item->expiry_date->format('Y-m-d') : '-' }}
                                            </td>
                                            <td>{{ $item->cost_price !== null ? number_format((float) $item->cost_price, 2) : '-' }}</td>
                                            <td>{{ $item->line_total !== null ? number_format((float) $item->line_total, 2) : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
