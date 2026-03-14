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
            <div class="ui-alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="ui-alert-warning">
                {{ session('warning') }}
            </div>
        @endif

        @if (session('error'))
            <div class="ui-alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- Row 1: Stock Configuration (left) + Add Products (right) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Stock Configuration Card --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Stock Configuration</h3>
                    <div class="space-y-4">
                        {{-- Branch Selection --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Branch</label>
                            @if ($isSuperAdmin)
                                <select wire:model.live="branch_id" class="ui-select">
                                    <option value="0">{{ __('Select...') }}</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            @else
                                <div class="rounded-lg border border-slate-300/80 bg-white/60 px-3 py-2 text-sm text-slate-700">
                                    {{ $branches->first()?->name ?? '-' }}
                                </div>
                            @endif
                        </div>

                        {{-- Received Date --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Received Date</label>
                            <input type="date" wire:model.defer="received_at_date" class="ui-input" />
                            @error('received_at_date') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        {{-- Supplier --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Supplier (optional)</label>
                            <input type="text" wire:model.defer="supplier_name" class="ui-input" placeholder="Enter supplier name..." />
                            @error('supplier_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        {{-- Batch Ref No --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Batch Ref No (optional)</label>
                            <input type="text" wire:model.defer="batch_ref_no" class="ui-input" placeholder="Enter batch reference..." />
                            @error('batch_ref_no') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Add Products Card --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Add Products</h3>

                    <div class="space-y-4">
                        {{-- Product Dropdown --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Product</label>
                            <input type="text" wire:model.live.debounce.300ms="product_search" class="ui-input mb-2" placeholder="Search product..." autocomplete="off" />
                            @if (count($searchableProducts) > 0)
                                <div class="border border-slate-300 rounded-md max-h-48 overflow-y-auto absolute z-10 bg-white w-full max-w-md">
                                    @foreach ($searchableProducts as $product)
                                        <button type="button" wire:click="selectProduct({{ $product->id }})" class="w-full text-left px-3 py-2 hover:bg-slate-100 border-b border-slate-100 last:border-b-0">
                                            <div class="font-medium">{{ $product->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $product->category?->name }}</div>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                            @if ($selectedProduct)
                                <div class="mt-2 p-2 bg-green-50 border border-green-200 rounded-md">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-medium text-green-800">{{ $selectedProduct->name }}</span>
                                            <span class="text-xs text-green-600 ml-2">{{ $selectedProduct->category?->name }}</span>
                                        </div>
                                        <button type="button" wire:click="$set('product_id', 0)" class="text-green-700 hover:text-green-900 text-sm">Clear</button>
                                    </div>
                                </div>
                            @endif
                            @if ($isSuperAdmin && $branch_id <= 0)
                                <div class="mt-1 text-xs text-slate-500">{{ __('Select a branch first to load products.') }}</div>
                            @endif
                        </div>

                        {{-- Entry Type Toggle --}}
                        @if ($selectedProduct && (bool) $selectedProduct->bulk_enabled)
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Entry Type</label>
                                <div class="inline-flex rounded-lg border border-slate-300/80 bg-white/60 p-1">
                                    <button type="button" wire:click="$set('entry_mode', 'unit')" class="px-4 py-2 text-sm font-medium rounded-md transition-all {{ $entry_mode === 'unit' ? 'bg-purple-500 text-white shadow-md' : 'text-slate-700 hover:bg-slate-100' }}">
                                        Units
                                    </button>
                                    <button type="button" wire:click="$set('entry_mode', 'bulk')" class="px-4 py-2 text-sm font-medium rounded-md transition-all {{ $entry_mode === 'bulk' ? 'bg-purple-500 text-white shadow-md' : 'text-slate-700 hover:bg-slate-100' }}">
                                        Bulk
                                    </button>
                                </div>
                            </div>
                        @endif

                        {{-- Quantity and Cost Price --}}
                        <div class="grid grid-cols-2 gap-4">
                            @if ($selectedProduct && (bool) $selectedProduct->bulk_enabled && $entry_mode === 'bulk')
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Bulk Quantity</label>
                                    <input type="number" min="1" wire:model.defer="bulk_quantity" class="ui-input font-mono" />
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ __('Units per bulk:') }} <span class="font-medium">{{ (int) ($selectedProduct?->bulkType?->units_per_bulk ?? 0) }}</span>
                                        {{ __('• Total:') }} <span class="font-medium">{{ (int) $bulk_quantity * (int) ($selectedProduct?->bulkType?->units_per_bulk ?? 0) }}</span>
                                    </div>
                                    @error('bulk_quantity') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                </div>
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-2">Quantity</label>
                                    <input type="number" min="1" wire:model.defer="quantity" class="ui-input font-mono" />
                                    @error('quantity') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">
                                    @if ($selectedProduct && (bool) $selectedProduct->bulk_enabled && $entry_mode === 'bulk')
                                        Cost per Bulk
                                    @else
                                        Cost per Unit
                                    @endif
                                </label>
                                <input type="number" min="0" step="0.01" wire:model.defer="cost_price" class="ui-input font-mono" placeholder="0.00" />
                                @error('cost_price') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        {{-- Expiry Date --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Expiry Date (optional)</label>
                            <input type="date" wire:model.defer="expiry_date" class="ui-input" />
                            @error('expiry_date') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        {{-- Add Line Button --}}
                        <button type="button" wire:click="addDraftLine" class="w-full bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-medium py-3 px-4 rounded-lg shadow-md transition-all">
                            + Add Line
                        </button>

                        {{-- Draft Lines List --}}
                        @php($draftItems = array_values($draft_lines))
                        @if (count($draftItems) > 0)
                            <div class="border-t border-slate-200 pt-4 space-y-3">
                                @foreach ($draftItems as $line)
                                    @php($isBulk = (string) ($line['entry_mode'] ?? 'unit') === 'bulk')
                                    @php($displayQty = $isBulk ? (int) ($line['bulk_quantity'] ?? 0) : (int) ($line['quantity'] ?? 0))
                                    @php($lineTotal = (($line['cost_price'] ?? null) !== null && ($line['cost_price'] ?? '') !== '') ? ((float) $line['cost_price'] * (int) ($line['quantity'] ?? 0)) : null)
                                    <div class="flex items-start justify-between p-3 bg-slate-50 rounded-lg">
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center gap-2">
                                                <span class="font-medium text-slate-900 truncate">{{ $line['name'] ?? '-' }}</span>
                                                @if ($isBulk)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                        Bulk
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="mt-1 text-sm text-slate-600">
                                                {{ $displayQty }} @if($isBulk) bulk ({{ (int) ($line['quantity'] ?? 0) }} units) @endif
                                                @if(($line['cost_price'] ?? null) !== null)
                                                    × {{ number_format((float) $line['cost_price'], 2) }}
                                                @endif
                                            </div>
                                            @if(($line['supplier_name'] ?? null) || ($line['batch_ref_no'] ?? null))
                                                <div class="mt-1 text-xs text-slate-500">
                                                    @if($line['supplier_name'] ?? null) {{ $line['supplier_name'] }} @endif
                                                    @if($line['batch_ref_no'] ?? null) • Batch: {{ $line['batch_ref_no'] }} @endif
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-3 ml-4">
                                            @if ($lineTotal !== null)
                                                <div class="font-mono font-bold text-purple-600">
                                                    {{ number_format((float) $lineTotal, 2) }}
                                                </div>
                                            @endif
                                            <button type="button" wire:click="removeDraftLine({{ (int) $line['key'] }})" class="text-red-500 hover:text-red-700 font-bold text-lg">×</button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @error('draft_lines')
                            <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Row 2: Stock Details (left) + Stock Summary (right) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
            {{-- Stock Details Card --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Additional Details</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Notes (optional)</label>
                            <textarea wire:model.defer="notes" rows="3" class="ui-input" placeholder="Add any notes about this stock receipt..."></textarea>
                            @error('notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Stock Summary Card --}}
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Stock Summary</h3>

                    @php($draftTotalQty = collect($draftItems)->sum('quantity'))
                    @php($draftTotalCost = collect($draftItems)->reduce(function ($carry, $row) { $cp = $row['cost_price'] ?? null; $qty = (int) ($row['quantity'] ?? 0); return $carry + (($cp !== null && $cp !== '') ? ((float) $cp * $qty) : 0); }, 0))

                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-slate-200">
                            <span class="text-slate-600">Total Items</span>
                            <span class="font-medium text-slate-900">{{ count($draftItems) }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2 border-b border-slate-200">
                            <span class="text-slate-600">Total Quantity</span>
                            <span class="font-medium text-purple-600">{{ (int) $draftTotalQty }}</span>
                        </div>

                        <div class="flex items-center justify-between py-2">
                            <span class="text-slate-600">Total Cost</span>
                            <span class="text-2xl font-bold text-purple-600 font-mono">{{ number_format((float) $draftTotalCost, 2) }}</span>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-4 mt-6 pt-4 border-t border-slate-200">
                        <button type="button" wire:click="clearDraftLines" class="flex-1 border-2 border-red-500 text-red-500 font-medium py-3 px-6 rounded-lg hover:bg-red-50 transition-all">
                            Clear Lines
                        </button>
                        <button type="button" wire:click="save" class="flex-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-3 px-8 rounded-lg shadow-lg transition-all">
                            Post Receipt
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
                                        <th>{{ __('Supplier') }}</th>
                                        <th>{{ __('Batch') }}</th>
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
                                            <td>{{ $item->supplier_name ?: '-' }}</td>
                                            <td>{{ $item->batch_ref_no ?: '-' }}</td>
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

                                    @if ($selectedReceipt->items->isEmpty())
                                        <tr>
                                            <td colspan="7" class="ui-table-empty">{{ __('No items found.') }}</td>
                                        </tr>
                                    @endif
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

{{-- Order your soul. Reduce your wants. - Augustine --}}
