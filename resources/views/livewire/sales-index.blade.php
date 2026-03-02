<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Sales') }}</h2>
            <div class="ui-page-subtitle">{{ __('Record sales and manage existing receipts.') }}</div>
            <div class="mt-4 flex items-center gap-3">
                <a href="{{ route('sales.download-template') }}" class="ui-btn-secondary">
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
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="ui-card-title">{{ __('Record Sale') }}</h3>

                            <div class="ui-tabs" role="tablist">
                                <button type="button"
                                    class="ui-tab {{ $sale_entry_type === 'single' ? 'ui-tab-active' : '' }}"
                                    wire:click="$set('sale_entry_type', 'single')"
                                    role="tab"
                                    aria-selected="{{ $sale_entry_type === 'single' ? 'true' : 'false' }}">
                                    {{ __('Single') }}
                                </button>
                                <button type="button"
                                    class="ui-tab {{ $sale_entry_type === 'group' ? 'ui-tab-active' : '' }}"
                                    wire:click="$set('sale_entry_type', 'group')"
                                    role="tab"
                                    aria-selected="{{ $sale_entry_type === 'group' ? 'true' : 'false' }}">
                                    {{ __('Group') }}
                                </button>
                            </div>
                        </div>

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
                                <select wire:model="product_id" class="ui-select">
                                    <option value="0">{{ __('Select...') }}</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="ui-label">{{ __('Entry Type') }}</label>
                                <div class="mt-1 inline-flex rounded-lg border border-slate-300/80 bg-white/60 p-1">
                                    <button type="button" wire:click="$set('entry_mode', 'unit')" class="px-3 py-2 text-sm font-medium rounded-md {{ $entry_mode === 'unit' ? 'bg-primary-blue text-white' : 'text-slate-700 hover:bg-soft-blue' }}" @if (!($selectedProduct && (bool) $selectedProduct->bulk_enabled)) disabled @endif>
                                        {{ __('Units') }}
                                    </button>
                                    <button type="button" wire:click="$set('entry_mode', 'bulk')" class="px-3 py-2 text-sm font-medium rounded-md {{ $entry_mode === 'bulk' ? 'bg-primary-blue text-white' : 'text-slate-700 hover:bg-soft-blue' }}" @if (!($selectedProduct && (bool) $selectedProduct->bulk_enabled)) disabled @endif>
                                        {{ __('Bulk') }}
                                    </button>
                                </div>
                            </div>

                            <div class="sm:col-span-2">
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
                                @else
                                    <label class="ui-label">{{ __('Quantity (Units)') }}</label>
                                    <input type="number" min="1" wire:model.defer="entry_quantity" class="mt-1 ui-input" />
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">
                                @if (($selectedProduct && (bool) $selectedProduct->bulk_enabled) && $entry_mode === 'bulk')
                                    {{ __('Price per Bulk') }}
                                @else
                                    {{ __('Price per Unit') }}
                                @endif
                            </label>
                            @php($entryUnitsPerBulk = (int) ($selectedProduct?->bulkType?->units_per_bulk ?? 0))
                            @php($entryPriceDisplay = (($selectedProduct && (bool) $selectedProduct->bulk_enabled) && $entry_mode === 'bulk' && $entryUnitsPerBulk > 0) ? number_format(((float) ($cart[$product_id]['unit_price'] ?? ($selectedProduct?->selling_price ?? 0)) * $entryUnitsPerBulk), 2, '.', '') : (string) ($cart[$product_id]['unit_price'] ?? ($selectedProduct?->selling_price ?? '0')))
                            <input type="number" min="0" step="0.01" class="mt-1 ui-input" value="{{ $entryPriceDisplay }}" wire:change="setUnitPrice({{ (int) $product_id }}, $event.target.value)" @if ($product_id <= 0) disabled @endif />
                        </div>

                        <div class="flex items-center justify-end">
                            <button type="button" wire:click="addProduct" class="ui-btn-primary">
                                {{ __('Add Item') }}
                            </button>
                        </div>

                        <div>
                            <div class="text-sm font-semibold text-slate-700">{{ __('Payment') }}</div>

                            <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="sm:col-span-3">
                                    <label class="ui-label">{{ __('Customer Name (optional)') }}</label>
                                    <input type="text" wire:model.defer="customer_name" class="mt-1 ui-input" />
                                    @error('customer_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="ui-label">{{ __('Method') }}</label>
                                    <div class="mt-1 rounded-lg border border-slate-300/80 bg-white/60 px-3 py-2 text-sm text-slate-700">
                                        {{ __('Cash') }}
                                    </div>
                                </div>

                                <div>
                                    <label class="ui-label">{{ __('Amount Paid') }}</label>
                                    <input type="number" min="0" step="0.01" wire:model.defer="amount_paid" class="mt-1 ui-input" />
                                    @error('amount_paid') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="mt-3">
                                <label class="ui-label">{{ __('Notes (optional)') }}</label>
                                <textarea wire:model.defer="notes" rows="2" class="mt-1 ui-input"></textarea>
                                @error('notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="mt-4 ui-muted-panel space-y-1">
                                <div class="flex items-center justify-between">
                                    <div>{{ __('Sub Total') }}</div>
                                    <div class="font-medium">{{ number_format((float) $subTotal, 2) }}</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>{{ __('Grand Total') }}</div>
                                    <div class="font-semibold text-slate-900">{{ number_format((float) $grandTotal, 2) }}</div>
                                </div>
                                <div class="flex items-center justify-between">
                                    <div>{{ __('Change Due') }}</div>
                                    <div class="font-medium">{{ number_format((float) $changeDue, 2) }}</div>
                                </div>
                            </div>

                            <div class="mt-4 flex items-center justify-end gap-3">
                                <button type="button" wire:click="clearCart" class="ui-btn-secondary">
                                    {{ __('Clear Items') }}
                                </button>
                                <button type="button" wire:click="finalizeSale" class="ui-btn-primary">
                                    {{ __('Post Sale') }}
                                </button>
                            </div>

                            @error('cart')
                                <div class="rounded-md bg-red-50 p-4 text-sm text-red-800">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
                </div>

                <div class="ui-card">
                    <div class="ui-card-body">
                        <div class="flex items-center justify-between">
                            <h3 class="ui-card-title">{{ __('Sale Items') }}</h3>
                            <div class="text-sm text-slate-500">
                                {{ __('Branch:') }}
                                <span class="font-medium">{{ $branches->firstWhere('id', $branch_id)?->name ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="mt-4 overflow-x-auto">
                            <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Available') }}</th>
                                        <th>{{ __('Qty') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Total') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cartItems as $item)
                                        @php($available = $stockMap[$item['product_id']] ?? 0)
                                        <tr wire:key="cart-{{ $item['product_id'] }}">
                                            <td>
                                                <div class="font-medium">{{ $item['name'] }}</div>
                                            </td>
                                            <td class="{{ $available < $item['quantity'] ? 'text-red-700' : '' }}">
                                                {{ $available }}
                                            </td>
                                            <td>
                                                <div class="inline-flex items-center gap-2">
                                                    <button type="button" wire:click="decrementItem({{ $item['product_id'] }})" class="ui-stepper-btn">-</button>
                                                    <input type="number" min="1" class="w-20 ui-input-compact" value="{{ (string) ($item['entry_mode'] ?? 'unit') === 'bulk' ? (int) ($item['bulk_quantity'] ?? 0) : (int) $item['quantity'] }}" wire:change="setQuantity({{ $item['product_id'] }}, $event.target.value)" />
                                                    <button type="button" wire:click="incrementItem({{ $item['product_id'] }})" class="ui-stepper-btn">+</button>
                                                </div>
                                                @if ((string) ($item['entry_mode'] ?? 'unit') === 'bulk')
                                                    <div class="mt-1 text-xs text-slate-500">{{ __('Units:') }} {{ (int) $item['quantity'] }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @php($isBulk = (string) ($item['entry_mode'] ?? 'unit') === 'bulk')
                                                @php($unitsPerBulk = (int) ($item['units_per_bulk'] ?? 0))
                                                @php($displayPrice = $isBulk && $unitsPerBulk > 0 ? number_format(((float) $item['unit_price'] * $unitsPerBulk), 2, '.', '') : (string) $item['unit_price'])
                                                <input type="number" min="0" step="0.01" class="w-28 ui-input-compact" value="{{ $displayPrice }}" wire:change="setUnitPrice({{ $item['product_id'] }}, $event.target.value)" />
                                                @if (($item['min_selling_price'] ?? null) !== null && ($item['min_selling_price'] ?? '') !== '')
                                                    <div class="mt-1 text-[11px] text-slate-500">{{ __('Min:') }} {{ number_format((float) $item['min_selling_price'], 2) }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                {{ number_format((float) $item['unit_price'] * (int) $item['quantity'], 2) }}
                                            </td>
                                            <td class="text-right">
                                                <button type="button" wire:click="removeItem({{ $item['product_id'] }})" class="ui-btn-link-danger">{{ __('Remove') }}</button>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if (count($cartItems) === 0)
                                        <tr>
                                            <td colspan="6" class="ui-table-empty">{{ __('No items added.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($mode === 'manage')
                <div class="ui-card">
                    <div class="ui-card-body">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="ui-card-title">{{ __('Manage Sales') }}</h3>
                            <div class="text-sm text-slate-500">
                                {{ __('Selected:') }}
                                <span class="font-medium">{{ count($selected_sales) }}</span>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="ui-label">{{ __('From') }}</label>
                                <input type="date" wire:model.live="sales_date_from" class="mt-1 ui-input" />
                            </div>
                            <div>
                                <label class="ui-label">{{ __('To') }}</label>
                                <input type="date" wire:model.live="sales_date_to" class="mt-1 ui-input" />
                            </div>
                            <div>
                                <label class="ui-label">{{ __('Status') }}</label>
                                <select wire:model.live="sales_status" class="mt-1 ui-select">
                                    <option value="active">{{ __('Active') }}</option>
                                    <option value="voided">{{ __('Voided') }}</option>
                                    <option value="all">{{ __('All') }}</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Search') }}</label>
                                <input type="text" wire:model.live.debounce.300ms="sales_search" placeholder="{{ __('Receipt / Branch / User') }}" class="mt-1 ui-input" />
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <button type="button" wire:click="selectAllSalesForDay('{{ $sales_date_from }}')" class="ui-btn-secondary">
                                    {{ __('Select All For Day') }}
                                </button>
                                @if (count($selected_sales) > 0)
                                    <button type="button" wire:click="clearSelectedSales" class="ui-btn-secondary">
                                        {{ __('Clear Selection') }}
                                    </button>
                                @endif
                            </div>

                            <div class="flex items-center gap-3">
                                @if (count($selected_sales) > 0)
                                    <a href="{{ route('sales.print_batch', ['ids' => implode(',', $selected_sales)]) }}" target="_blank" class="ui-btn-primary">
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
                                        <th>{{ __('Total') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($sales as $sale)
                                        <tr wire:key="sale-{{ $sale->id }}">
                                            <td>
                                                <input type="checkbox" value="{{ $sale->id }}" wire:model.live="selected_sales" class="ui-checkbox" />
                                            </td>
                                            <td>
                                                <div class="font-medium">{{ $sale->receipt_no }}</div>
                                                <div class="text-xs text-slate-500">{{ $sale->sold_at?->format('Y-m-d H:i') }}</div>
                                            </td>
                                            <td>
                                                {{ $sale->branch?->name ?? '-' }}
                                            </td>
                                            <td>
                                                {{ number_format((float) $sale->grand_total, 2) }}
                                            </td>
                                            <td>
                                                @if ($sale->voided_at)
                                                    <span class="ui-badge-warning">{{ __('Voided') }}</span>
                                                @else
                                                    <span class="ui-badge-success">{{ __('Active') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="inline-flex items-center gap-3">
                                                    <button type="button" wire:click="openSaleModal({{ $sale->id }})" class="ui-btn-link">{{ __('View') }}</button>
                                                    @if (! $sale->voided_at)
                                                        <button type="button" wire:click="openEditModal({{ $sale->id }})" class="ui-btn-link">{{ __('Edit') }}</button>
                                                        <button type="button" wire:click="openVoidModal({{ $sale->id }})" class="ui-btn-link-danger">{{ __('Void') }}</button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if ($sales->isEmpty())
                                        <tr>
                                            <td colspan="6" class="ui-table-empty">{{ __('No sales found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            </div>

                            @if (method_exists($sales, 'hasPages') && $sales->hasPages())
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="text-sm text-slate-600">
                                        {{ __('Showing') }} {{ $sales->firstItem() }} {{ __('to') }} {{ $sales->lastItem() }} {{ __('of') }} {{ $sales->total() }} {{ __('results') }}
                                    </div>
                                    {{ $sales->links('pagination::tailwind') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if ($show_sale_modal && $selectedSale)
            <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeSaleModal" data-modal-overlay></div>
                <div class="relative w-full max-w-3xl mx-4 ui-card">
                    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Sale Details') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ $selectedSale->receipt_no }}</div>
                            <div class="mt-1 text-sm text-slate-600">
                                {{ $selectedSale->branch?->name ?? '-' }}
                                @if ($selectedSale->user)
                                    {{ '• ' . $selectedSale->user->name }}
                                @endif
                                {{ '• ' . $selectedSale->sold_at?->format('Y-m-d H:i') }}
                            </div>
                            @if ($selectedSale->customer_name)
                                <div class="mt-1 text-sm text-slate-600">
                                    {{ __('Customer:') }} {{ $selectedSale->customer_name }}
                                </div>
                            @endif
                        </div>

                        <button type="button" wire:click="closeSaleModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                    </div>

                    <div class="p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-slate-700">
                                {{ __('Payment:') }}
                                <span class="font-medium">{{ strtoupper($selectedSale->payment_method) }}</span>
                            </div>
                            <a href="{{ route('sales.print', $selectedSale->id) }}" target="_blank" class="ui-btn-secondary">
                                {{ __('Print Receipt') }}
                            </a>
                        </div>

                        @if ($selectedSale->notes)
                            <div class="mt-2 text-sm text-slate-700">{{ $selectedSale->notes }}</div>
                        @endif

                        <div class="mt-4 overflow-x-auto">
                            <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Qty') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($selectedSale->items as $item)
                                        <tr wire:key="sale-modal-item-{{ $item->id }}">
                                            <td>{{ $item->product?->name ?? '-' }}</td>
                                            <td>
                                                @if ((string) $item->entry_mode === 'bulk')
                                                    {{ (int) ($item->bulk_quantity ?? 0) }} {{ __('bulk') }}
                                                    <span class="text-xs text-slate-500">({{ (int) $item->quantity }} {{ __('units') }})</span>
                                                @else
                                                    {{ (int) $item->quantity }}
                                                @endif
                                            </td>
                                            <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                                            <td>{{ number_format((float) $item->line_total, 2) }}</td>
                                        </tr>
                                    @endforeach

                                    @if ($selectedSale->items->isEmpty())
                                        <tr>
                                            <td colspan="4" class="ui-table-empty">{{ __('No items found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            </div>
                        </div>

                        <div class="mt-4 ui-muted-panel space-y-1">
                            <div class="flex items-center justify-between">
                                <div>{{ __('Grand Total') }}</div>
                                <div class="font-semibold text-slate-900">{{ number_format((float) $selectedSale->grand_total, 2) }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>{{ __('COGS') }}</div>
                                <div class="font-medium">{{ number_format((float) $selectedSale->cogs_total, 2) }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>{{ __('Profit') }}</div>
                                <div class="font-medium">{{ number_format((float) $selectedSale->profit_total, 2) }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>{{ __('Paid') }}</div>
                                <div class="font-medium">{{ number_format((float) $selectedSale->amount_paid, 2) }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>{{ __('Change') }}</div>
                                <div class="font-medium">{{ number_format((float) $selectedSale->change_due, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($show_edit_modal)
            <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeEditModal" data-modal-overlay></div>
                <div class="relative w-full max-w-5xl mx-4 ui-card">
                    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Edit Sale') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ __('Full Edit') }}</div>
                        </div>
                        <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                    </div>

                    <div class="p-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
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
                                    <input type="number" min="1" wire:model.defer="edit_entry_quantity" class="mt-1 ui-input" />
                                @endif
                            </div>
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
                                        <th>{{ __('Available') }}</th>
                                        <th>{{ __('Qty') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Total') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($editCartItems as $item)
                                        @php($available = $editStockMap[$item['product_id']] ?? 0)
                                        <tr wire:key="edit-cart-{{ $item['product_id'] }}">
                                            <td>
                                                <div class="font-medium">{{ $item['name'] }}</div>
                                            </td>
                                            <td class="{{ $available < (int) $item['quantity'] ? 'text-red-700' : '' }}">
                                                {{ $available }}
                                            </td>
                                            <td>
                                                <div class="inline-flex items-center gap-2">
                                                    <button type="button" wire:click="decrementEditItem({{ $item['product_id'] }})" class="ui-stepper-btn">-</button>
                                                    <input type="number" min="1" class="w-20 ui-input-compact" value="{{ (string) ($item['entry_mode'] ?? 'unit') === 'bulk' ? (int) ($item['bulk_quantity'] ?? 0) : (int) $item['quantity'] }}" wire:change="setEditQuantity({{ $item['product_id'] }}, $event.target.value)" />
                                                    <button type="button" wire:click="incrementEditItem({{ $item['product_id'] }})" class="ui-stepper-btn">+</button>
                                                </div>
                                                @if ((string) ($item['entry_mode'] ?? 'unit') === 'bulk')
                                                    <div class="mt-1 text-xs text-slate-500">{{ __('Units:') }} {{ (int) $item['quantity'] }}</div>
                                                @endif
                                            </td>
                                            <td>
                                                @php($isBulk = (string) ($item['entry_mode'] ?? 'unit') === 'bulk')
                                                @php($unitsPerBulk = (int) ($item['units_per_bulk'] ?? 0))
                                                @php($displayPrice = $isBulk && $unitsPerBulk > 0 ? number_format(((float) $item['unit_price'] * $unitsPerBulk), 2, '.', '') : (string) $item['unit_price'])
                                                <input type="number" min="0" step="0.01" class="w-28 ui-input-compact" value="{{ $displayPrice }}" wire:change="setEditUnitPrice({{ $item['product_id'] }}, $event.target.value)" />
                                            </td>
                                            <td>
                                                {{ number_format((float) $item['unit_price'] * (int) $item['quantity'], 2) }}
                                            </td>
                                            <td class="text-right">
                                                <button type="button" wire:click="removeEditItem({{ $item['product_id'] }})" class="ui-btn-link-danger">{{ __('Remove') }}</button>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if (count($editCartItems) === 0)
                                        <tr>
                                            <td colspan="6" class="ui-table-empty">{{ __('Cart is empty.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Customer Name (optional)') }}</label>
                                <input type="text" wire:model.defer="edit_customer_name" class="mt-1 ui-input" />
                                @error('edit_customer_name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="ui-label">{{ __('Method') }}</label>
                                <div class="mt-1 rounded-lg border border-slate-300/80 bg-white/60 px-3 py-2 text-sm text-slate-700">
                                    {{ __('Cash') }}
                                </div>
                            </div>
                            <div>
                                <label class="ui-label">{{ __('Amount Paid') }}</label>
                                <input type="number" min="0" step="0.01" wire:model.defer="edit_amount_paid" class="mt-1 ui-input" />
                                @error('edit_amount_paid') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Notes (optional)') }}</label>
                            <textarea wire:model.defer="edit_notes" rows="2" class="mt-1 ui-input"></textarea>
                        </div>

                        <div class="ui-muted-panel space-y-1">
                            <div class="flex items-center justify-between">
                                <div>{{ __('Sub Total') }}</div>
                                <div class="font-medium">{{ number_format((float) $editSubTotal, 2) }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>{{ __('Grand Total') }}</div>
                                <div class="font-semibold text-slate-900">{{ number_format((float) $editGrandTotal, 2) }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>{{ __('Change Due') }}</div>
                                <div class="font-medium">{{ number_format((float) $editChangeDue, 2) }}</div>
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
                            <div class="text-sm text-slate-500">{{ __('Void Sale') }}</div>
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
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" wire:click="closeVoidModal" class="ui-btn-secondary">{{ __('Cancel') }}</button>
                            <button type="button" wire:click="confirmVoidSale" class="ui-btn-danger">{{ __('Void') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
