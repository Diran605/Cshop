<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Sales Records') }}</h2>
            <div class="ui-page-subtitle">{{ __('View, edit, void, and print sales records.') }}</div>
        </div>

        @if (session('status'))
            <div class="ui-alert-success">
                {{ session('status') }}
            </div>
        @endif

        <!-- Filters -->
        <div class="ui-card mb-6">
            <div class="ui-card-body">
                <div class="grid grid-cols-1 md:grid-cols-6 gap-4">
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
                        <label class="ui-label">{{ __('From') }}</label>
                        <input type="date" wire:model.live="date_from" class="mt-1 ui-input" />
                    </div>

                    <div>
                        <label class="ui-label">{{ __('To') }}</label>
                        <input type="date" wire:model.live="date_to" class="mt-1 ui-input" />
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Payment') }}</label>
                        <select wire:model.live="payment_filter" class="mt-1 ui-select">
                            <option value="all">{{ __('All Methods') }}</option>
                            <option value="cash">{{ __('Cash') }}</option>
                            <option value="mobile">{{ __('Mobile') }}</option>
                            <option value="card">{{ __('Card') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Status') }}</label>
                        <select wire:model.live="status_filter" class="mt-1 ui-select">
                            <option value="all">{{ __('All Statuses') }}</option>
                            <option value="active">{{ __('Active') }}</option>
                            <option value="void_pending">{{ __('Void Pending') }}</option>
                            <option value="voided">{{ __('Voided') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Search') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="mt-1 ui-input" placeholder="{{ __('Receipt # or customer...') }}" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="ui-card">
            <div class="ui-card-header">
                <h3 class="ui-card-title">{{ __('Sales Records') }}</h3>
                <div class="flex items-center gap-4">
                    <div class="text-sm text-slate-500">
                        {{ __('Selected:') }} <span class="font-medium">{{ count($selected_sales) }}</span>
                    </div>
                    <div class="text-sm text-slate-500">
                        {{ $receipts->total() }} {{ __('records') }}
                    </div>
                </div>
            </div>

            <div class="ui-card-body border-b border-slate-200">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    @can('sales_records.batch_print')
                        <div class="flex items-center gap-3">
                            @if ($date_from)
                                <button type="button" wire:click="selectAllSalesForDay('{{ $date_from }}')" class="ui-btn-secondary">
                                    {{ __('Select All For Day') }}
                                </button>
                            @endif
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
                    @else
                        <div></div>
                    @endcan
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="ui-table text-sm">
                    <thead>
                        <tr>
                            @can('sales_records.batch_print')
                                <th class="w-8"></th>
                            @endcan
                            <th class="whitespace-nowrap">{{ __('Receipt') }}</th>
                            @if ($isSuperAdmin)
                                <th>{{ __('Branch') }}</th>
                            @endif
                            <th class="whitespace-nowrap">{{ __('Date') }}</th>
                            <th>{{ __('Customer') }}</th>
                            <th class="text-center">{{ __('Items') }}</th>
                            <th class="text-right">{{ __('Total') }}</th>
                            <th class="text-center">{{ __('Status') }}</th>
                            <th class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($receipts as $receipt)
                            <tr wire:key="sale-{{ $receipt->id }}" class="{{ $receipt->voided_at ? 'bg-red-50 opacity-60' : ($receipt->void_requested_at ? 'bg-yellow-50' : '') }}">
                                @can('sales_records.batch_print')
                                    <td class="text-center">
                                        @if (! $receipt->voided_at && ! $receipt->void_requested_at)
                                            <input type="checkbox" value="{{ $receipt->id }}" wire:model.live="selected_sales" class="ui-checkbox" />
                                        @endif
                                    </td>
                                @endcan
                                <td class="font-mono whitespace-nowrap">{{ $receipt->receipt_no }}</td>
                                @if ($isSuperAdmin)
                                    <td class="whitespace-nowrap">{{ $receipt->branch?->name ?? '-' }}</td>
                                @endif
                                <td class="whitespace-nowrap">{{ $receipt->sold_at?->format('M j, H:i') ?? '-' }}</td>
                                <td class="max-w-[120px] truncate">{{ $receipt->customer_name ?? '-' }}</td>
                                <td class="text-center">{{ $receipt->items->count() }}</td>
                                <td class="text-right font-mono text-green-700 whitespace-nowrap">
                                    XAF {{ number_format((float) $receipt->grand_total, 0) }}
                                </td>
                                <td class="text-center">
                                    @if ($receipt->voided_at)
                                        <span class="ui-badge-warning">{{ __('Voided') }}</span>
                                    @elseif ($receipt->void_requested_at)
                                        <span class="ui-badge-info">{{ __('Void Pending') }}</span>
                                    @else
                                        <span class="ui-badge-success">{{ __('Active') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="inline-flex items-center gap-1">
                                        <button type="button" wire:click="viewSale({{ $receipt->id }})" class="ui-btn-link text-xs">{{ __('View') }}</button>
                                        @if (! $receipt->voided_at && ! $receipt->void_requested_at)
                                            @can('sales_records.edit')
                                                <button type="button" wire:click="openEditModal({{ $receipt->id }})" class="ui-btn-link text-xs">{{ __('Edit') }}</button>
                                            @endcan
                                            @can('sales_records.void')
                                                <button type="button" wire:click="openVoidModal({{ $receipt->id }})" class="ui-btn-link-danger text-xs">{{ __('Void') }}</button>
                                            @endcan
                                        @endif
                                        @can('sales_records.print')
                                            <a href="{{ route('sales.print', $receipt->id) }}" target="_blank" class="ui-btn-link text-xs">{{ __('Print') }}</a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 9 : 8 }}" class="text-center py-8 text-slate-500">
                                    {{ __('No sales records found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($receipts->hasPages())
                <div class="ui-card-footer">
                    {{ $receipts->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- View Sale Modal -->
    @if ($show_view_modal && $viewing_receipt)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeViewModal" data-modal-overlay></div>
            <div class="relative w-full max-w-3xl ui-card max-h-[90vh] overflow-y-auto">
                <div class="p-4 border-b border-slate-200 sticky top-0 bg-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Sales Receipt') }}</div>
                            <div class="mt-1 font-semibold text-slate-900 font-mono">{{ $viewing_receipt->receipt_no }}</div>
                        </div>
                        <button type="button" wire:click="closeViewModal" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    <!-- Receipt Info -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Branch') }}</div>
                            <div class="font-medium">{{ $viewing_receipt->branch?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Date') }}</div>
                            <div class="font-medium">{{ $viewing_receipt->sold_at?->format('M j, Y H:i') ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Cashier') }}</div>
                            <div class="font-medium">{{ $viewing_receipt->user?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Customer') }}</div>
                            <div class="font-medium">{{ $viewing_receipt->customer_name ?? '-' }}</div>
                        </div>
                    </div>

                    @if ($viewing_receipt->voided_at)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <div class="text-sm font-medium text-red-800">{{ __('This receipt has been voided') }}</div>
                                    <div class="text-xs text-red-600 mt-1">
                                        {{ __('Voided at:') }} {{ $viewing_receipt->voided_at?->format('M j, Y H:i') ?? '-' }}
                                        {{ __('by:') }} {{ $viewing_receipt->voidedBy?->name ?? '-' }}
                                        @if ($viewing_receipt->void_reason)
                                            <br>{{ __('Reason:') }} {{ $viewing_receipt->void_reason }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @elseif ($viewing_receipt->void_requested_at)
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div>
                                    <div class="text-sm font-medium text-yellow-800">{{ __('Void request pending approval') }}</div>
                                    <div class="text-xs text-yellow-700 mt-1">
                                        {{ __('Requested at:') }} {{ $viewing_receipt->void_requested_at?->format('M j, Y H:i') ?? '-' }}
                                        {{ __('by:') }} {{ $viewing_receipt->voidRequestedBy?->name ?? '-' }}
                                        @if ($viewing_receipt->void_reason)
                                            <br>{{ __('Reason:') }} {{ $viewing_receipt->void_reason }}
                                        @endif
                                        <br><em>{{ __('Stock adjustments pending manager approval.') }}</em>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Items Table -->
                    <div>
                        <div class="text-sm font-medium text-slate-700 mb-2">{{ __('Items') }}</div>
                        <div class="overflow-x-auto">
                            <table class="ui-table text-sm">
                                <thead>
                                    <tr>
                                        <th>{{ __('Product') }}</th>
                                        <th class="text-right">{{ __('Qty') }}</th>
                                        <th class="text-right">{{ __('Price') }}</th>
                                        <th class="text-right">{{ __('Total') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($viewing_receipt->items as $item)
                                        <tr>
                                            <td>{{ $item->product?->name ?? '-' }}</td>
                                            <td class="text-right font-mono">{{ (int) $item->quantity }}</td>
                                            <td class="text-right font-mono">XAF {{ number_format((float) $item->unit_price, 2) }}</td>
                                            <td class="text-right font-mono">XAF {{ number_format((float) $item->line_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="border-t border-slate-200 pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">{{ __('Sub Total') }}</span>
                            <span class="font-mono">XAF {{ number_format((float) $viewing_receipt->sub_total, 2) }}</span>
                        </div>
                        @if ($viewing_receipt->discount_total > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">{{ __('Discount') }}</span>
                                <span class="font-mono text-red-600">- XAF {{ number_format((float) $viewing_receipt->discount_total, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between text-lg font-semibold">
                            <span>{{ __('Grand Total') }}</span>
                            <span class="font-mono text-green-700">XAF {{ number_format((float) $viewing_receipt->grand_total, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">{{ __('Amount Paid') }}</span>
                            <span class="font-mono">XAF {{ number_format((float) $viewing_receipt->amount_paid, 2) }}</span>
                        </div>
                        @if ($viewing_receipt->change_due > 0)
                            <div class="flex justify-between text-sm">
                                <span class="text-slate-600">{{ __('Change Due') }}</span>
                                <span class="font-mono">XAF {{ number_format((float) $viewing_receipt->change_due, 2) }}</span>
                            </div>
                        @endif
                    </div>

                    @if ($viewing_receipt->notes)
                        <div class="bg-slate-50 rounded-lg p-3">
                            <div class="text-xs text-slate-500 mb-1">{{ __('Notes') }}</div>
                            <div class="text-sm text-slate-700">{{ $viewing_receipt->notes }}</div>
                        </div>
                    @endif
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-end gap-3 sticky bottom-0 bg-white">
                    <a href="{{ route('sales.print', $viewing_receipt->id) }}" target="_blank" class="ui-btn-secondary">
                        {{ __('Print') }}
                    </a>
                    <button type="button" wire:click="closeViewModal" class="ui-btn-primary">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Sale Modal -->
    @if ($show_edit_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeEditModal" data-modal-overlay></div>
            <div class="relative w-full max-w-4xl ui-card max-h-[90vh] overflow-y-auto">
                <div class="p-4 border-b border-slate-200 sticky top-0 bg-white z-10">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Edit Sale') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ __('Modify sale items and details') }}</div>
                        </div>
                        <button type="button" wire:click="closeEditModal" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    @if ($errors->any())
                        <div class="ui-alert-danger">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Add Product -->
                    <div class="bg-slate-50 rounded-lg p-4">
                        <div class="text-sm font-medium text-slate-700 mb-3">{{ __('Add Product') }}</div>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Product') }}</label>
                                <select wire:model.live="edit_product_id" class="mt-1 ui-select">
                                    <option value="0">{{ __('Select product...') }}</option>
                                    @foreach ($editProducts as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="ui-label">{{ __('Mode') }}</label>
                                <select wire:model.live="edit_entry_mode" class="mt-1 ui-select">
                                    <option value="unit">{{ __('Unit') }}</option>
                                    <option value="bulk">{{ __('Bulk') }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="ui-label">{{ __('Qty') }}</label>
                                <div class="flex gap-2">
                                    <input type="number" wire:model.live="edit_entry_quantity" min="1" class="mt-1 ui-input" />
                                    <button type="button" wire:click="addEditProduct" class="mt-1 ui-btn-primary">
                                        {{ __('Add') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cart Items -->
                    <div>
                        <div class="text-sm font-medium text-slate-700 mb-2">{{ __('Items') }}</div>
                        @if (count($edit_cart) > 0)
                            <div class="overflow-x-auto">
                                <table class="ui-table text-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Product') }}</th>
                                            <th class="text-right">{{ __('Qty') }}</th>
                                            <th class="text-right">{{ __('Price') }}</th>
                                            <th class="text-right">{{ __('Total') }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $editSubTotal = 0;
                                        @endphp
                                        @foreach ($edit_cart as $productId => $item)
                                            @php
                                                $lineTotal = (float) $item['unit_price'] * (int) $item['quantity'];
                                                $editSubTotal += $lineTotal;
                                            @endphp
                                            <tr wire:key="edit-item-{{ $productId }}">
                                                <td>{{ $item['name'] }}</td>
                                                <td class="text-right">
                                                    <div class="flex items-center justify-end gap-1">
                                                        <button type="button" wire:click="decrementEditItem({{ $productId }})" class="text-slate-500 hover:text-slate-700">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                                                            </svg>
                                                        </button>
                                                        <input type="number" value="{{ $item['quantity'] }}" wire:change="setEditQuantity({{ $productId }}, $event.target.value)" class="w-16 text-center ui-input" />
                                                        <button type="button" wire:click="incrementEditItem({{ $productId }})" class="text-slate-500 hover:text-slate-700">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <input type="number" step="0.01" value="{{ $item['unit_price'] }}" wire:change="setEditUnitPrice({{ $productId }}, $event.target.value)" class="w-24 text-right ui-input" />
                                                </td>
                                                <td class="text-right font-mono">XAF {{ number_format($lineTotal, 2) }}</td>
                                                <td>
                                                    <button type="button" wire:click="removeEditItem({{ $productId }})" class="text-red-500 hover:text-red-700">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8 text-slate-500">
                                {{ __('No items in cart.') }}
                            </div>
                        @endif
                    </div>

                    <!-- Payment Details -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="ui-label">{{ __('Amount Paid') }}</label>
                            <input type="number" step="0.01" wire:model.live="edit_amount_paid" class="mt-1 ui-input" />
                        </div>
                        <div>
                            <label class="ui-label">{{ __('Customer Name') }}</label>
                            <input type="text" wire:model.live="edit_customer_name" class="mt-1 ui-input" />
                        </div>
                        <div>
                            <label class="ui-label">{{ __('Notes') }}</label>
                            <input type="text" wire:model.live="edit_notes" class="mt-1 ui-input" />
                        </div>
                        <div>
                            <label class="ui-label">{{ __('Edit Reason') }} <span class="text-red-500">*</span></label>
                            <textarea wire:model.defer="edit_reason" rows="2" class="mt-1 ui-input" placeholder="{{ __('Why are you editing this sale?') }}"></textarea>
                            @error('edit_reason') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="border-t border-slate-200 pt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">{{ __('Sub Total') }}</span>
                            <span class="font-mono">XAF {{ number_format($editSubTotal ?? 0, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold">
                            <span>{{ __('Grand Total') }}</span>
                            <span class="font-mono text-green-700">XAF {{ number_format($editSubTotal ?? 0, 2) }}</span>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-end gap-3 sticky bottom-0 bg-white">
                    <button type="button" wire:click="closeEditModal" class="ui-btn-secondary">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" wire:click="saveEdit" class="ui-btn-primary">
                        {{ __('Save Changes') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Void Sale Modal -->
    @if ($show_void_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeVoidModal" data-modal-overlay></div>
            <div class="relative w-full max-w-md ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <div class="text-lg font-semibold text-yellow-600">{{ __('Request Void') }}</div>
                        <button type="button" wire:click="closeVoidModal" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    @if ($errors->any())
                        <div class="ui-alert-danger">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                        <div class="flex items-start gap-2">
                            <svg class="w-5 h-5 text-yellow-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="text-sm text-yellow-800">
                                <strong>{{ __('Approval Required') }}</strong><br>
                                {{ __('This void request will be sent for manager approval. Pending adjustments will be created to restore stock. The sale will be locked until approved or rejected.') }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Void Reason') }} <span class="text-red-500">*</span></label>
                        <textarea wire:model.live="void_reason" rows="3" class="mt-1 ui-input" placeholder="{{ __('Minimum 10 characters. E.g., "Wrong products entered on this sale"') }}" required></textarea>
                        <div class="text-xs text-slate-500 mt-1">{{ __('Min 10 characters') }}</div>
                        @error('void_reason')
                            <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-end gap-3">
                    <button type="button" wire:click="closeVoidModal" class="ui-btn-secondary">
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" wire:click="confirmVoidSale" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 font-medium">
                        {{ __('Submit Void Request') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
