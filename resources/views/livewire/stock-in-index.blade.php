<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Stock In') }}</h2>
            <div class="ui-page-subtitle">{{ __('Receive inventory and review stock in records.') }}</div>
            <div class="mt-4 flex items-center gap-3">
                <a href="{{ route('stock_in.download-template') }}" class="ui-btn-secondary">
                    {{ __('Download Template') }}
                </a>
                <label class="ui-btn-secondary cursor-pointer">
                    {{ __('Import Excel') }}
                    <input type="file" wire:model="excel_file" accept=".xlsx,.xls" class="hidden" />
                </label>
                @if ($excel_file)
                    <button type="button" wire:click="importExcel" class="ui-btn-primary">
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
            @if ($mode === 'add')
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
                                <label class="ui-label">{{ __('Product') }}</label>
                                <div class="mt-1 space-y-2">
                                    <input type="text" wire:model.live.debounce.300ms="product_search" class="ui-input" placeholder="Search product..." />
                                    <select wire:model="product_id" @disabled($isSuperAdmin && $branch_id <= 0) class="ui-select">
                                        <option value="0">{{ __('Select...') }}</option>
                                        @foreach ($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
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

                            <div class="flex flex-wrap items-center justify-end gap-3">
                                <button type="button" wire:click="addDraftLine" class="ui-btn-secondary">
                                    {{ __('Add Line') }}
                                </button>
                                <button type="button" wire:click="save" class="ui-btn-primary">
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
            @endif

            @if ($mode === 'manage')
                <div class="space-y-6">
                    <div class="ui-card">
                        <div class="ui-card-body">
                            <div class="flex items-center justify-between">
                                <h3 class="ui-card-title">{{ __('Current Stock') }}</h3>
                                <div class="text-sm text-slate-500">
                                    {{ __('Branch:') }}
                                    <span class="font-medium">{{ $branches->firstWhere('id', $branch_id)?->name ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="mt-3 max-w-md">
                                <label class="ui-label">{{ __('Search') }}</label>
                                <input type="text" wire:model.live.debounce.300ms="stock_search" class="mt-1 ui-input" placeholder="Search stock..." />
                            </div>

                            <div class="mt-4 overflow-x-auto">
                                <div class="ui-table-wrap">
                                <table class="ui-table">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Product') }}</th>
                                            <th>{{ __('Current') }}</th>
                                            <th>{{ __('Min') }}</th>
                                            <th>{{ __('Cost') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($stocks as $stock)
                                            <tr wire:key="stock-{{ $stock->id }}">
                                                <td>
                                                    {{ $stock->product?->name ?? '-' }}
                                                </td>
                                                <td>
                                                    {{ $stock->current_stock }}
                                                </td>
                                                <td>
                                                    {{ $stock->minimum_stock }}
                                                </td>
                                                <td>
                                                    {{ $stock->cost_price !== null ? number_format((float) $stock->cost_price, 2) : '-' }}
                                                </td>
                                            </tr>
                                        @endforeach

                                        @if ($stocks->isEmpty())
                                            <tr>
                                                <td colspan="4" class="ui-table-empty">{{ __('No stock rows found for this branch.') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="ui-card">
                    <div class="ui-card-body">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="ui-card-title">{{ __('Manage Stock In') }}</h3>
                            <div class="text-sm text-slate-500">
                                {{ __('Selected:') }}
                                <span class="font-medium">{{ count($selected_receipts) }}</span>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="ui-label">{{ __('From') }}</label>
                                <input type="date" wire:model.live="receipt_date_from" class="mt-1 ui-input" />
                            </div>
                            <div>
                                <label class="ui-label">{{ __('To') }}</label>
                                <input type="date" wire:model.live="receipt_date_to" class="mt-1 ui-input" />
                            </div>
                            <div>
                                <label class="ui-label">{{ __('Status') }}</label>
                                <select wire:model.live="receipt_status" class="mt-1 ui-select">
                                    <option value="active">{{ __('Active') }}</option>
                                    <option value="voided">{{ __('Voided') }}</option>
                                    <option value="all">{{ __('All') }}</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Search') }}</label>
                                <input type="text" wire:model.live.debounce.300ms="receipt_search" placeholder="{{ __('Receipt / Branch / User') }}" class="mt-1 ui-input" />
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <button type="button" wire:click="selectAllReceiptsForDay('{{ $receipt_date_from }}')" class="ui-btn-secondary">
                                    {{ __('Select All For Day') }}
                                </button>
                                @if (count($selected_receipts) > 0)
                                    <button type="button" wire:click="clearSelectedReceipts" class="ui-btn-secondary">
                                        {{ __('Clear Selection') }}
                                    </button>
                                @endif
                            </div>

                            <div class="flex items-center gap-3">
                                @if (count($selected_receipts) > 0)
                                    <a href="{{ route('stock_in.print_batch', ['ids' => implode(',', $selected_receipts)]) }}" target="_blank" class="ui-btn-primary">
                                        {{ __('Print Selected') }}
                                    </a>
                                @else
                                    <button type="button" class="ui-btn-secondary" disabled>
                                        {{ __('Print Selected') }}
                                    </button>
                                @endif
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th>{{ __('Receipt') }}</th>
                                        <th>{{ __('Branch') }}</th>
                                        <th>{{ __('Qty') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($receipts as $receipt)
                                        <tr wire:key="receipt-{{ $receipt->id }}">
                                            <td>
                                                <input type="checkbox" value="{{ $receipt->id }}" wire:model.live="selected_receipts" class="ui-checkbox" />
                                            </td>
                                            <td>
                                                <div class="font-medium">{{ $receipt->receipt_no }}</div>
                                                <div class="text-xs text-slate-500">{{ $receipt->received_at?->format('Y-m-d H:i') }}</div>
                                            </td>
                                            <td>
                                                {{ $receipt->branch?->name ?? '-' }}
                                            </td>
                                            <td>
                                                {{ $receipt->total_quantity }}
                                            </td>
                                            <td>
                                                @if ($receipt->voided_at)
                                                    <span class="ui-badge-warning">{{ __('Voided') }}</span>
                                                @else
                                                    <span class="ui-badge-success">{{ __('Active') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="inline-flex items-center justify-end gap-2">
                                                    <button type="button" wire:click="openReceiptModal({{ $receipt->id }})" class="ui-btn-link">{{ __('View') }}</button>

                                                    @if (! $receipt->voided_at)
                                                        <button type="button" wire:click="openEditModal({{ $receipt->id }})" class="ui-btn-link">{{ __('Edit') }}</button>
                                                        <button type="button" wire:click="openVoidModal({{ $receipt->id }})" class="ui-btn-link-danger">{{ __('Void') }}</button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if ($receipts->isEmpty())
                                        <tr>
                                            <td colspan="6" class="ui-table-empty">{{ __('No receipts found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            </div>

                            @if (method_exists($receipts, 'hasPages') && $receipts->hasPages())
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="text-sm text-slate-600">
                                        {{ __('Showing') }} {{ $receipts->firstItem() }} {{ __('to') }} {{ $receipts->lastItem() }} {{ __('of') }} {{ $receipts->total() }} {{ __('results') }}
                                    </div>
                                    {{ $receipts->links('pagination::tailwind') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>

        @if ($show_receipt_modal && $selectedReceipt)
            <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeReceiptModal" data-modal-overlay></div>
                <div class="relative w-full max-w-3xl mx-4 ui-card">
                    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Receipt Details') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ $selectedReceipt->receipt_no }}</div>
                            <div class="mt-1 text-sm text-slate-600">
                                {{ $selectedReceipt->branch?->name ?? '-' }}
                                @if ($selectedReceipt->user)
                                    {{ '• ' . $selectedReceipt->user->name }}
                                @endif
                                {{ '• ' . $selectedReceipt->received_at?->format('Y-m-d H:i') }}
                            </div>
                        </div>

                        <button type="button" wire:click="closeReceiptModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                    </div>

                    <div class="p-4">
                        @if ($selectedReceipt->voided_at)
                            <div class="mb-4 rounded-md bg-yellow-50 p-4 text-sm text-yellow-900">
                                <div class="font-semibold">{{ __('Voided') }}</div>
                                <div class="mt-1">{{ __('Voided at:') }} {{ $selectedReceipt->voided_at?->format('Y-m-d H:i') }}</div>
                                @if ($selectedReceipt->void_reason)
                                    <div class="mt-1">{{ __('Reason:') }} {{ $selectedReceipt->void_reason }}</div>
                                @endif
                            </div>
                        @endif

                        <div class="flex items-center justify-between">
                            <div></div>
                            <a href="{{ route('stock_in.print', $selectedReceipt->id) }}" target="_blank" class="ui-btn-secondary">
                                {{ __('Print Receipt') }}
                            </a>
                        </div>

                        @if ($selectedReceipt->notes)
                            <div class="text-sm text-slate-700">{{ $selectedReceipt->notes }}</div>
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

        @if ($show_edit_modal)
            @php($editCartItems = array_values($edit_cart))
            @php($editTotalQty = collect($editCartItems)->sum('quantity'))
            @php($editTotalCost = collect($editCartItems)->reduce(function ($carry, $row) { $cp = $row['cost_price'] ?? null; $qty = (int) ($row['quantity'] ?? 0); return $carry + (($cp !== null && $cp !== '') ? ((float) $cp * $qty) : 0); }, 0))

            <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeEditModal" data-modal-overlay></div>
                <div class="relative w-full max-w-5xl mx-4 ui-card">
                    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Edit Stock In Receipt') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ __('Full Edit') }}</div>
                        </div>
                        <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                    </div>

                    <div class="p-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Product') }}</label>
                                <select wire:model="edit_product_id" class="mt-1 ui-select">
                                    <option value="0">{{ __('Select...') }}</option>
                                    @foreach ($editProducts as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Entry Type') }}</label>
                                <div class="mt-1 inline-flex rounded-lg border border-slate-300/80 bg-white/60 p-1">
                                    <button type="button" wire:click="$set('edit_entry_mode', 'unit')" class="px-3 py-2 text-sm font-medium rounded-md {{ $edit_entry_mode === 'unit' ? 'bg-primary-blue text-white' : 'text-slate-700 hover:bg-soft-blue' }}">
                                        {{ __('Units') }}
                                    </button>
                                    <button type="button" wire:click="$set('edit_entry_mode', 'bulk')" class="px-3 py-2 text-sm font-medium rounded-md {{ $edit_entry_mode === 'bulk' ? 'bg-primary-blue text-white' : 'text-slate-700 hover:bg-soft-blue' }}">
                                        {{ __('Bulk') }}
                                    </button>
                                </div>
                            </div>

                            <div>
                                @if ($edit_entry_mode === 'bulk')
                                    <label class="ui-label">{{ __('Bulk Quantity') }}</label>
                                    <input type="number" min="1" wire:model.defer="edit_bulk_quantity" class="mt-1 ui-input" />
                                @else
                                    <label class="ui-label">{{ __('Quantity (Units)') }}</label>
                                    <input type="number" min="1" wire:model.defer="edit_quantity" class="mt-1 ui-input" />
                                @endif
                            </div>

                            <div>
                                <label class="ui-label">
                                    @if ($edit_entry_mode === 'bulk')
                                        {{ __('Cost Price per Bulk') }}
                                    @else
                                        {{ __('Cost Price per Unit') }}
                                    @endif
                                </label>
                                <input type="number" min="0" step="0.01" wire:model.defer="edit_cost_price" class="mt-1 ui-input" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Expiry Date (optional)') }}</label>
                                <input type="date" wire:model.defer="edit_expiry_date" class="mt-1 ui-input" />
                            </div>

                            <div class="md:col-span-3">
                                <label class="ui-label">{{ __('Supplier (optional)') }}</label>
                                <input type="text" wire:model.defer="edit_supplier_name" class="mt-1 ui-input" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Batch Ref No (optional)') }}</label>
                                <input type="text" wire:model.defer="edit_batch_ref_no" class="mt-1 ui-input" />
                            </div>
                            <div class="md:col-span-3"></div>
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="button" wire:click="addEditProduct" class="ui-btn-primary">{{ __('Add') }}</button>
                        </div>

                        @error('edit_cart')
                            <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $message }}</div>
                        @enderror

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
                                    @foreach ($editCartItems as $item)
                                        @php($isBulk = (string) ($item['entry_mode'] ?? 'unit') === 'bulk')
                                        @php($displayQty = $isBulk ? (int) ($item['bulk_quantity'] ?? 0) : (int) ($item['quantity'] ?? 0))
                                        @php($unitsPerBulk = (int) ($item['units_per_bulk'] ?? 0))
                                        @php($displayCost = $isBulk ? ((($item['cost_price'] ?? null) !== null && ($item['cost_price'] ?? '') !== '' && $unitsPerBulk > 0) ? number_format(((float) $item['cost_price'] * $unitsPerBulk), 2, '.', '') : '') : (string) ($item['cost_price'] ?? ''))
                                        @php($lineTotal = (($item['cost_price'] ?? null) !== null && ($item['cost_price'] ?? '') !== '') ? ((float) $item['cost_price'] * (int) $item['quantity']) : null)
                                        <tr wire:key="edit-receipt-item-{{ (int) ($item['key'] ?? 0) }}">
                                            <td class="text-slate-900">
                                                <div class="font-medium">{{ $item['name'] ?? '-' }}</div>
                                                @if ($isBulk)
                                                    <div class="mt-1 text-xs text-slate-500">{{ __('Units:') }} {{ (int) ($item['quantity'] ?? 0) }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                <input type="text" class="w-44 ui-input-compact" value="{{ (string) ($item['supplier_name'] ?? '') }}" wire:change="setEditSupplierName({{ (int) ($item['key'] ?? 0) }}, $event.target.value)" />
                                            </td>
                                            <td>
                                                <input type="text" class="w-36 ui-input-compact" value="{{ (string) ($item['batch_ref_no'] ?? '') }}" wire:change="setEditBatchRefNo({{ (int) ($item['key'] ?? 0) }}, $event.target.value)" />
                                            </td>
                                            <td>
                                                <div class="inline-flex items-center gap-2">
                                                    <button type="button" wire:click="decrementEditItem({{ (int) ($item['key'] ?? 0) }})" class="ui-stepper-btn">-</button>
                                                    <input type="number" min="1" class="w-24 ui-input-compact" value="{{ $displayQty }}" wire:change="setEditQuantity({{ (int) ($item['key'] ?? 0) }}, $event.target.value)" />
                                                    <button type="button" wire:click="incrementEditItem({{ (int) ($item['key'] ?? 0) }})" class="ui-stepper-btn">+</button>
                                                </div>
                                            </td>
                                            <td>
                                                {{ ($item['expiry_date'] ?? null) ?: '-' }}
                                            </td>
                                            <td>
                                                <input type="number" min="0" step="0.01" class="w-28 ui-input-compact" value="{{ $displayCost }}" wire:change="setEditCostPrice({{ (int) ($item['key'] ?? 0) }}, $event.target.value)" />
                                            </td>
                                            <td>
                                                {{ $lineTotal !== null ? number_format((float) $lineTotal, 2) : '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-right">
                                                <button type="button" wire:click="removeEditItem({{ (int) ($item['key'] ?? 0) }})" class="ui-btn-link-danger">{{ __('Remove') }}</button>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if (count($editCartItems) === 0)
                                        <tr>
                                            <td colspan="8" class="ui-table-empty">{{ __('Cart is empty.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Notes (optional)') }}</label>
                            <textarea wire:model.defer="edit_notes" rows="2" class="mt-1 ui-input"></textarea>
                        </div>

                        <div class="ui-muted-panel space-y-1">
                            <div class="flex items-center justify-between">
                                <div>{{ __('Total Qty') }}</div>
                                <div class="font-medium">{{ (int) $editTotalQty }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>{{ __('Total Cost') }}</div>
                                <div class="font-semibold text-slate-900">{{ number_format((float) $editTotalCost, 2) }}</div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <button type="button" wire:click="closeEditModal" class="ui-btn-secondary">{{ __('Cancel') }}</button>
                            <button type="button" wire:click="saveEdit" class="ui-btn-primary">{{ __('Save Changes') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($show_void_modal)
            <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeVoidModal" data-modal-overlay></div>
                <div class="relative w-full max-w-lg mx-4 ui-card">
                    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Void Stock In Receipt') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ __('Confirm Void') }}</div>
                        </div>
                        <button type="button" wire:click="closeVoidModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                    </div>

                    <div class="p-4 space-y-4">
                        <div class="text-sm text-slate-700">
                            {{ __('This will reverse stock for all items and mark the receipt as voided.') }}
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Reason (optional)') }}</label>
                            <textarea wire:model.defer="void_reason" rows="2" class="mt-1 ui-input"></textarea>
                            @error('void_reason') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <button type="button" wire:click="closeVoidModal" class="ui-btn-secondary">{{ __('Cancel') }}</button>
                            <button type="button" wire:click="confirmVoidReceipt" class="ui-btn-danger">{{ __('Void') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Order your soul. Reduce your wants. - Augustine --}}
</div>
