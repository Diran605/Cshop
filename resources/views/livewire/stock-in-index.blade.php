<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Stock In') }}</h2>
            <div class="ui-page-subtitle">{{ __('Receive inventory into stock.') }}</div>
            <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <a href="{{ route('stock_in.download-template') }}" class="ui-btn-secondary w-full sm:w-auto text-center">
                    {{ __('Download Template') }}
                </a>
                <label class="ui-btn-secondary cursor-pointer w-full sm:w-auto text-center">
                    {{ __('Import Excel') }}
                    <input type="file" wire:model="excel_file" accept=".xlsx,.xls" class="hidden" />
                </label>
                @if ($excel_file)
                    <button type="button" wire:click="importExcel" class="ui-btn-primary w-full sm:w-auto">
                        {{ __('Upload') }}
                    </button>
                @endif
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

        <div class="space-y-6">
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title">{{ __('Add Stock In') }}</h3>

                        <div class="mt-4 space-y-4">
                            <div>
                                <label class="ui-label">{{ __('Branch') }}</label>
                                @if ($isSuperAdmin)
                                    <select wire:model.live="branch_id" class="mt-1 ui-select">
                                        <option value="0">{{ __('Select...') }}</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                @else
                                    <div class="mt-1 rounded-lg border border-slate-300/80 bg-white/60 px-3 py-2 text-sm text-slate-700">
                                        {{ $branches->first()?->name ?? '-' }}
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Received Date') }}</label>
                                <input type="date" wire:model.defer="received_at_date" class="mt-1 ui-input" />
                                @error('received_at_date') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Product') }}</label>
                                <div class="mt-1 space-y-2">
                                    <input type="text" wire:model.live.debounce.300ms="product_search" class="ui-input" placeholder="Search product..." />
                                    <select wire:key="products-{{ $branch_id }}-{{ md5($product_search) }}" wire:model="product_id" @disabled($isSuperAdmin && $branch_id <= 0) class="ui-select">
                                        <option value="0">{{ __('Select...') }}</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}@if($product->category) ({{ $product->category->name }})@endif</option>
                                        @endforeach
                                    </select>
                                </div>
                                @error('product_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror

                                @if ($isSuperAdmin && $branch_id <= 0)
                                    <div class="mt-1 text-xs text-slate-500">{{ __('Select a branch first to load products.') }}</div>
                                @endif
                            </div>

                            @if ($selectedProduct && (bool) $selectedProduct->bulk_enabled)
                                <div>
                                    <label class="ui-label">{{ __('Entry Type') }}</label>
                                    <div class="mt-1 inline-flex rounded-lg border border-slate-300/80 bg-white/60 p-1">
                                        <button type="button" wire:click="$set('entry_mode', 'unit')" class="px-3 py-2 text-sm font-medium rounded-md {{ $entry_mode === 'unit' ? 'bg-primary-blue text-white' : 'text-slate-700 hover:bg-soft-blue' }}">
                                            {{ __('Units') }}
                                        </button>
                                        <button type="button" wire:click="$set('entry_mode', 'bulk')" class="px-3 py-2 text-sm font-medium rounded-md {{ $entry_mode === 'bulk' ? 'bg-primary-blue text-white' : 'text-slate-700 hover:bg-soft-blue' }}">
                                            {{ __('Bulk') }}
                                        </button>
                                    </div>
                                    @error('entry_mode') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                </div>
                            @endif

                            <div>
                                @if (($selectedProduct && (bool) $selectedProduct->bulk_enabled) && $entry_mode === 'bulk')
                                    <label class="ui-label">{{ __('Bulk Quantity') }}</label>
                                    <input type="number" min="1" wire:model.defer="bulk_quantity" class="mt-1 ui-input" />
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ __('Units per bulk:') }}
                                        <span class="font-medium">
                                            {{ (int) ($selectedProduct?->bulkType?->units_per_bulk ?? 0) }}
                                            {{ $selectedProduct?->bulkType?->bulkUnit?->name ? '(' . $selectedProduct->bulkType->bulkUnit->name . ')' : '' }}
                                        </span>
                                        {{ __('• Total units:') }}
                                        <span class="font-medium">{{ (int) $bulk_quantity * (int) ($selectedProduct?->bulkType?->units_per_bulk ?? 0) }}</span>
                                    </div>
                                    @error('bulk_quantity') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                @else
                                    <label class="ui-label">{{ __('Quantity (Units)') }}</label>
                                    <input type="number" min="1" wire:model.defer="quantity" class="mt-1 ui-input" />
                                    @error('quantity') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                @endif
                            </div>

                            <div>
                                <label class="ui-label">
                                    @if (($selectedProduct && (bool) $selectedProduct->bulk_enabled) && $entry_mode === 'bulk')
                                        {{ __('Cost Price per Bulk (optional)') }}
                                    @else
                                        {{ __('Cost Price per Unit (optional)') }}
                                    @endif
                                </label>
                                <input type="number" min="0" step="0.01" wire:model.defer="cost_price" class="mt-1 ui-input" />
                                @error('cost_price') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Supplier (optional)') }}</label>
                                <input type="text" wire:model.defer="supplier_name" class="mt-1 ui-input" />
                                @error('supplier_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Batch Ref No (optional)') }}</label>
                                <input type="text" wire:model.defer="batch_ref_no" class="mt-1 ui-input" />
                                @error('batch_ref_no') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Expiry Date (optional)') }}</label>
                                <input type="date" wire:model.defer="expiry_date" class="mt-1 ui-input" />
                                @error('expiry_date') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                                <button type="button" wire:click="addDraftLine" class="ui-btn-secondary w-full sm:w-auto">
                                    {{ __('Add Line') }}
                                </button>
                                <button type="button" wire:click="save" class="ui-btn-primary w-full sm:w-auto">
                                    {{ __('Post Receipt') }}
                                </button>
                            </div>

                            @error('draft_lines')
                                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $message }}</div>
                            @enderror

                            @php($draftItems = array_values($draft_lines))
                            @php($draftTotalQty = collect($draftItems)->sum('quantity'))
                            @php($draftTotalCost = collect($draftItems)->reduce(function ($carry, $row) { $cp = $row['cost_price'] ?? null; $qty = (int) ($row['quantity'] ?? 0); return $carry + (($cp !== null && $cp !== '') ? ((float) $cp * $qty) : 0); }, 0))

                            <div class="overflow-x-auto">
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
                                                <th class="px-4 py-3"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($draftItems as $line)
                                                @php($isBulk = (string) ($line['entry_mode'] ?? 'unit') === 'bulk')
                                                @php($displayQty = $isBulk ? (int) ($line['bulk_quantity'] ?? 0) : (int) ($line['quantity'] ?? 0))
                                                @php($unitsPerBulk = (int) ($line['units_per_bulk'] ?? 0))
                                                @php($displayCost = $isBulk ? ((($line['cost_price'] ?? null) !== null && ($line['cost_price'] ?? '') !== '' && $unitsPerBulk > 0) ? number_format(((float) $line['cost_price'] * $unitsPerBulk), 2, '.', '') : '') : (string) ($line['cost_price'] ?? ''))
                                                @php($lineTotal = (($line['cost_price'] ?? null) !== null && ($line['cost_price'] ?? '') !== '') ? ((float) $line['cost_price'] * (int) ($line['quantity'] ?? 0)) : null)
                                                <tr wire:key="draft-line-{{ $line['key'] }}">
                                                    <td class="text-slate-900">
                                                        <div class="font-medium">{{ $line['name'] ?? '-' }}</div>
                                                        @if ($isBulk)
                                                            <div class="mt-1 text-xs text-slate-500">{{ __('Units:') }} {{ (int) ($line['quantity'] ?? 0) }}</div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <input type="text" class="w-44 ui-input-compact" value="{{ (string) ($line['supplier_name'] ?? '') }}" wire:change="setDraftLineSupplierName({{ (int) $line['key'] }}, $event.target.value)" />
                                                    </td>
                                                    <td>
                                                        <input type="text" class="w-36 ui-input-compact" value="{{ (string) ($line['batch_ref_no'] ?? '') }}" wire:change="setDraftLineBatchRefNo({{ (int) $line['key'] }}, $event.target.value)" />
                                                    </td>
                                                    <td>
                                                        <div class="inline-flex items-center gap-2">
                                                            <button type="button" wire:click="decrementDraftLine({{ (int) $line['key'] }})" class="ui-stepper-btn">-</button>
                                                            <input type="number" min="1" class="w-24 ui-input-compact" value="{{ $displayQty }}" wire:change="setDraftLineQuantity({{ (int) $line['key'] }}, $event.target.value)" />
                                                            <button type="button" wire:click="incrementDraftLine({{ (int) $line['key'] }})" class="ui-stepper-btn">+</button>
                                                            @if (!empty($line['unit_type_name'] ?? null))
                                                                <span class="text-xs text-slate-500">{{ $line['unit_type_name'] }}</span>
                                                            @endif
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <input type="date" class="w-44 ui-input-compact" value="{{ (string) ($line['expiry_date'] ?? '') }}" wire:change="setDraftLineExpiryDate({{ (int) $line['key'] }}, $event.target.value)" />
                                                    </td>
                                                    <td>
                                                        <input type="number" min="0" step="0.01" class="w-28 ui-input-compact" value="{{ $displayCost }}" wire:change="setDraftLineCostPrice({{ (int) $line['key'] }}, $event.target.value)" />
                                                    </td>
                                                    <td>
                                                        {{ $lineTotal !== null ? number_format((float) $lineTotal, 2) : '-' }}
                                                    </td>
                                                    <td class="px-4 py-3 text-sm text-right">
                                                        <button type="button" wire:click="removeDraftLine({{ (int) $line['key'] }})" class="ui-btn-link-danger">{{ __('Remove') }}</button>
                                                    </td>
                                                </tr>
                                            @endforeach

                                            @if (count($draftItems) === 0)
                                                <tr>
                                                    <td colspan="8" class="ui-table-empty">{{ __('No lines added.') }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Notes (optional)') }}</label>
                                <textarea wire:model.defer="notes" rows="2" class="mt-1 ui-input"></textarea>
                                @error('notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="ui-muted-panel space-y-1">
                                <div class="flex items-center justify-between">
                                    <div>{{ __('Total Qty') }}</div>
                                    <div class="font-medium">{{ (int) $draftTotalQty }}</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>{{ __('Total Cost') }}</div>
                                    <div class="font-semibold text-slate-900">{{ number_format((float) $draftTotalCost, 2) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($selectedReceipt)
            <div wire:key="receipt-modal-{{ $selectedReceipt->id }}" class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="selectReceipt(0)" data-modal-overlay></div>
                <div class="relative w-full max-w-3xl mx-4 ui-card">
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

{{-- Order your soul. Reduce your wants. - Augustine --}}
