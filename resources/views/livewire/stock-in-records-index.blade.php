<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Stock In Records') }}</h2>
            <div class="ui-page-subtitle">{{ __('View, edit, void, and print stock in receipts.') }}</div>
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
                        <label class="ui-label">{{ __('From') }}</label>
                        <input type="date" wire:model.live="date_from" class="mt-1 ui-input" />
                    </div>

                    <div>
                        <label class="ui-label">{{ __('To') }}</label>
                        <input type="date" wire:model.live="date_to" class="mt-1 ui-input" />
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
                        <input type="text" wire:model.live.debounce.300ms="search" class="mt-1 ui-input" placeholder="{{ __('Receipt # or supplier...') }}" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Stock In Table -->
        <div class="ui-card">
            <div class="ui-card-header">
                <h3 class="ui-card-title">{{ __('Stock In Receipts') }}</h3>
                <div class="flex items-center gap-4">
                    <div class="text-sm text-slate-500">
                        {{ __('Selected:') }} <span class="font-medium">{{ count($selected_receipts) }}</span>
                    </div>
                    <div class="text-sm text-slate-500">
                        {{ $receipts->total() }} {{ __('records') }}
                    </div>
                </div>
            </div>

            <div class="ui-card-body border-b border-slate-200">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    @can('stock_in.batch_print')
                        <div class="flex items-center gap-3">
                            @if ($date_from)
                                <button type="button" wire:click="selectAllReceiptsForDay('{{ $date_from }}')" class="ui-btn-secondary">
                                    {{ __('Select All For Day') }}
                                </button>
                            @endif
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
                    @else
                        <div></div>
                    @endcan
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="ui-table text-sm">
                    <thead>
                        <tr>
                            @can('stock_in.batch_print')
                                <th class="w-8"></th>
                            @endcan
                            <th class="whitespace-nowrap">{{ __('Receipt') }}</th>
                            @if ($isSuperAdmin)
                                <th>{{ __('Branch') }}</th>
                            @endif
                            <th class="whitespace-nowrap">{{ __('Date') }}</th>
                            <th>{{ __('Supplier') }}</th>
                            <th class="text-center">{{ __('Items') }}</th>
                            <th class="text-center">{{ __('Qty') }}</th>
                            <th class="text-right">{{ __('Total Cost') }}</th>
                            <th class="text-center">{{ __('Status') }}</th>
                            <th class="text-center">{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($receipts as $receipt)
                            @php
                                $supplierNames = $receipt->items->pluck('supplier_name')->unique()->filter()->implode(', ');
                                $totalQty = $receipt->items->sum('quantity');
                            @endphp
                            <tr wire:key="receipt-{{ $receipt->id }}" class="{{ $receipt->voided_at ? 'bg-red-50 opacity-60' : ($receipt->void_requested_at ? 'bg-yellow-50' : '') }}">
                                @can('stock_in.batch_print')
                                    <td class="text-center">
                                        @if (! $receipt->voided_at && ! $receipt->void_requested_at)
                                            <input type="checkbox" value="{{ $receipt->id }}" wire:model.live="selected_receipts" class="ui-checkbox" />
                                        @endif
                                    </td>
                                @endcan
                                <td class="font-mono whitespace-nowrap">{{ $receipt->receipt_no }}</td>
                                @if ($isSuperAdmin)
                                    <td class="whitespace-nowrap">{{ $receipt->branch?->name ?? '-' }}</td>
                                @endif
                                <td class="whitespace-nowrap">{{ $receipt->received_at?->format('M j, Y H:i') ?? '-' }}</td>
                                <td class="max-w-[120px] truncate">{{ $supplierNames ?: '-' }}</td>
                                <td class="text-center">{{ $receipt->items->count() }}</td>
                                <td class="text-center font-mono">{{ $totalQty }}</td>
                                <td class="text-right font-mono text-slate-700 whitespace-nowrap">
                                    XAF {{ number_format((float) $receipt->total_cost, 0) }}
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
                                        <button type="button" wire:click="viewReceipt({{ $receipt->id }})" class="ui-btn-link text-xs">{{ __('View') }}</button>
                                        @if (! $receipt->voided_at && ! $receipt->void_requested_at)
                                            @can('stock_in.edit')
                                                <button type="button" wire:click="openEditModal({{ $receipt->id }})" class="ui-btn-link text-xs">{{ __('Edit') }}</button>
                                            @endcan
                                            @can('stock_in.void')
                                                <button type="button" wire:click="openVoidModal({{ $receipt->id }})" class="ui-btn-link-danger text-xs">{{ __('Void') }}</button>
                                            @endcan
                                        @endif
                                        @can('stock_in.print')
                                            <a href="{{ route('stock_in.print', $receipt->id) }}" target="_blank" class="ui-btn-link text-xs">{{ __('Print') }}</a>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isSuperAdmin ? 10 : 9 }}" class="text-center py-8 text-slate-500">
                                    {{ __('No stock in records found.') }}
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

    <!-- View Receipt Modal -->
    @if ($show_view_modal && $viewing_receipt)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeViewModal" data-modal-overlay></div>
            <div class="relative w-full max-w-3xl ui-card max-h-[90vh] overflow-y-auto">
                <div class="p-4 border-b border-slate-200 sticky top-0 bg-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Stock In Receipt') }}</div>
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
                            <div class="text-xs text-slate-500">{{ __('Received At') }}</div>
                            <div class="font-medium">{{ $viewing_receipt->received_at?->format('M j, Y H:i') ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Received By') }}</div>
                            <div class="font-medium">{{ $viewing_receipt->user?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Total Items') }}</div>
                            <div class="font-medium">{{ $viewing_receipt->items->count() }}</div>
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
                                        <th class="text-right">{{ __('Remaining') }}</th>
                                        <th class="text-right">{{ __('Cost') }}</th>
                                        <th class="text-right">{{ __('Total') }}</th>
                                        <th>{{ __('Supplier') }}</th>
                                        <th>{{ __('Expiry') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($viewing_receipt->items as $item)
                                        <tr>
                                            <td>
                                                {{ $item->product?->name ?? '-' }}
                                                @if ($item->product?->unitType)
                                                    <span class="text-xs text-slate-500">({{ $item->product->unitType->name }})</span>
                                                @endif
                                            </td>
                                            <td class="text-right font-mono">{{ (int) $item->quantity }}</td>
                                            <td class="text-right font-mono">{{ (int) $item->remaining_quantity }}</td>
                                            <td class="text-right font-mono">XAF {{ number_format((float) $item->cost_price, 2) }}</td>
                                            <td class="text-right font-mono">XAF {{ number_format((float) $item->cost_price * (int) $item->quantity, 2) }}</td>
                                            <td>{{ $item->supplier_name ?? '-' }}</td>
                                            <td class="whitespace-nowrap">
                                                @if ($item->expiry_date)
                                                    @if (Carbon\Carbon::parse($item->expiry_date)->isPast())
                                                        <span class="text-red-600">{{ $item->expiry_date->format('M j, Y') }}</span>
                                                    @elseif (Carbon\Carbon::parse($item->expiry_date)->diffInDays(now()) <= 30)
                                                        <span class="text-orange-600">{{ $item->expiry_date->format('M j, Y') }}</span>
                                                    @else
                                                        {{ $item->expiry_date->format('M j, Y') }}
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="border-t border-slate-200 pt-4 space-y-2">
                        <div class="flex justify-between text-lg font-semibold">
                            <span>{{ __('Total Quantity') }}</span>
                            <span class="font-mono">{{ $viewing_receipt->items->sum('quantity') }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-semibold">
                            <span>{{ __('Total Cost') }}</span>
                            <span class="font-mono text-slate-700">XAF {{ number_format((float) $viewing_receipt->total_cost, 2) }}</span>
                        </div>
                    </div>

                    @if ($viewing_receipt->notes)
                        <div class="bg-slate-50 rounded-lg p-3">
                            <div class="text-xs text-slate-500 mb-1">{{ __('Notes') }}</div>
                            <div class="text-sm text-slate-700">{{ $viewing_receipt->notes }}</div>
                        </div>
                    @endif
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-end gap-3 sticky bottom-0 bg-white">
                    <a href="{{ route('stock_in.print', $viewing_receipt->id) }}" target="_blank" class="ui-btn-secondary">
                        {{ __('Print') }}
                    </a>
                    <button type="button" wire:click="closeViewModal" class="ui-btn-primary">
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Edit Receipt Modal -->
    @if ($show_edit_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeEditModal" data-modal-overlay></div>
            <div class="relative w-full max-w-4xl ui-card max-h-[90vh] overflow-y-auto">
                <div class="p-4 border-b border-slate-200 sticky top-0 bg-white z-10">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Edit Stock In Receipt') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ __('Modify receipt items and details') }}</div>
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

                    <!-- Receipt Date -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="ui-label">{{ __('Received At') }}</label>
                            <input type="date" wire:model.live="edit_received_at_date" class="mt-1 ui-input" />
                        </div>
                        <div>
                            <label class="ui-label">{{ __('Notes') }}</label>
                            <input type="text" wire:model.live="edit_notes" class="mt-1 ui-input" placeholder="{{ __('Optional notes...') }}" />
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div>
                        <div class="text-sm font-medium text-slate-700 mb-2">{{ __('Items') }}</div>
                        @if (count($edit_cart) > 0)
                            <div class="overflow-x-auto">
                                <table class="ui-table text-sm">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Product') }}</th>
                                            <th class="text-right">{{ __('Qty') }}</th>
                                            <th class="text-right">{{ __('Remaining') }}</th>
                                            <th class="text-right">{{ __('Cost') }}</th>
                                            <th class="text-right">{{ __('Total') }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $editTotalCost = 0;
                                        @endphp
                                        @foreach ($edit_cart as $itemId => $item)
                                            @php
                                                $lineTotal = (float) $item['cost_price'] * (int) $item['quantity'];
                                                $editTotalCost += $lineTotal;
                                            @endphp
                                            <tr wire:key="edit-item-{{ $itemId }}">
                                                <td>
                                                    {{ $item['name'] }}
                                                    @if ($item['unit_type_name'])
                                                        <span class="text-xs text-slate-500">({{ $item['unit_type_name'] }})</span>
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    <input type="number" min="1" value="{{ $item['quantity'] }}" wire:change="setEditItemQuantity({{ $itemId }}, $event.target.value)" class="w-20 text-center ui-input" />
                                                </td>
                                                <td class="text-right font-mono">{{ $item['remaining_quantity'] }}</td>
                                                <td class="text-right">
                                                    <input type="number" step="0.01" min="0" value="{{ $item['cost_price'] }}" wire:change="setEditItemCostPrice({{ $itemId }}, $event.target.value)" class="w-24 text-right ui-input" />
                                                </td>
                                                <td class="text-right font-mono">XAF {{ number_format($lineTotal, 2) }}</td>
                                                <td>
                                                    <button type="button" wire:click="removeEditItem({{ $itemId }})" class="text-red-500 hover:text-red-700">
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
                                {{ __('No items in receipt.') }}
                            </div>
                        @endif
                    </div>

                    <!-- Totals -->
                    <div class="border-t border-slate-200 pt-4 space-y-2">
                        <div class="flex justify-between text-lg font-semibold">
                            <span>{{ __('Total Cost') }}</span>
                            <span class="font-mono text-slate-700">XAF {{ number_format($editTotalCost ?? 0, 2) }}</span>
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

    <!-- Void Receipt Modal -->
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
                                {{ __('This void request will be sent for manager approval. Pending adjustments will be created to reverse stock. The receipt will be locked until approved or rejected.') }}
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Void Reason') }} <span class="text-red-500">*</span></label>
                        <textarea wire:model.live="void_reason" rows="3" class="mt-1 ui-input" placeholder="{{ __('Minimum 10 characters. E.g., "Wrong quantities entered — supplier delivered 5 units not 20"') }}" required></textarea>
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
                    <button type="button" wire:click="confirmVoidReceipt" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 font-medium">
                        {{ __('Submit Void Request') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
