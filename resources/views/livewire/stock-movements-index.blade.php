<div>
    <div class="ui-page">
        <div class="ui-page-container">
            <div class="mb-6">
                <h2 class="ui-page-title">{{ __('Stock Movements') }}</h2>
                <div class="ui-page-subtitle">{{ __('Audit trail of inventory movements.') }}</div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                        <div>
                            <label class="ui-label">{{ __('From') }}</label>
                            <input type="date" wire:model.live="date_from" class="mt-1 ui-input" />
                        </div>

                        <div>
                            <label class="ui-label">{{ __('To') }}</label>
                            <input type="date" wire:model.live="date_to" class="mt-1 ui-input" />
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Branch') }}</label>
                            <select wire:model.live="branch_id" class="mt-1 ui-select" @if (! $isSuperAdmin) disabled @endif>
                                @if ($isSuperAdmin)
                                    <option value="0">{{ __('All') }}</option>
                                @endif
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Product') }}</label>
                            <select wire:model.live="product_id" class="mt-1 ui-select">
                                <option value="0">{{ __('All') }}</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Type') }}</label>
                            <select wire:model.live="movement_type" class="mt-1 ui-select">
                                <option value="all">{{ __('All') }}</option>
                                <option value="IN">{{ __('IN') }}</option>
                                <option value="OUT">{{ __('OUT') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Search') }}</label>
                            <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('Product/User') }}" class="mt-1 ui-input" />
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto">
                        <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Date') }}</th>
                                        <th>{{ __('Branch') }}</th>
                                        <th>{{ __('Product') }}</th>
                                        <th>{{ __('Type') }}</th>
                                        <th class="text-right">{{ __('Qty') }}</th>
                                        <th class="text-right">{{ __('Before') }}</th>
                                        <th class="text-right">{{ __('After') }}</th>
                                        <th class="text-right">{{ __('Unit Cost') }}</th>
                                        <th class="text-right">{{ __('Unit Price') }}</th>
                                        <th>{{ __('User') }}</th>
                                        <th>{{ __('Ref') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($movements as $m)
                                        <tr wire:key="mv-{{ $m->id }}">
                                            <td>{{ optional($m->moved_at)->format('Y-m-d H:i') }}</td>
                                            <td>{{ $m->branch?->name ?? '-' }}</td>
                                            <td class="text-slate-900">{{ $m->product?->name ?? '-' }}</td>
                                            <td class="font-medium {{ $m->movement_type === 'IN' ? 'text-green-700' : 'text-red-700' }}">{{ $m->movement_type }}</td>
                                            <td class="text-right text-slate-900">{{ (int) $m->quantity }}</td>
                                            <td class="text-right">{{ (int) $m->before_stock }}</td>
                                            <td class="text-right">{{ (int) $m->after_stock }}</td>
                                            <td class="text-right">{{ $m->unit_cost !== null ? number_format((float) $m->unit_cost, 2) : '-' }}</td>
                                            <td class="text-right">{{ $m->unit_price !== null ? number_format((float) $m->unit_price, 2) : '-' }}</td>
                                            <td>{{ $m->user?->name ?? '-' }}</td>
                                            <td>
                                                @if ($m->stock_in_receipt_id)
                                                    {{ __('SI') }} #{{ $m->stock_in_receipt_id }}
                                                @elseif ($m->sales_receipt_id)
                                                    {{ __('SL') }} #{{ $m->sales_receipt_id }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <button wire:click="openDetailModal({{ $m->id }})" class="ui-btn-link">
                                                    {{ __('View') }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if ($movements->isEmpty())
                                        <tr>
                                            <td colspan="12" class="ui-table-empty">{{ __('No movements found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            </div>

                            @if ($movements->hasPages())
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="text-sm text-slate-600">
                                        {{ __('Showing') }} {{ $movements->firstItem() }} {{ __('to') }} {{ $movements->lastItem() }} {{ __('of') }} {{ $movements->total() }} {{ __('results') }}
                                    </div>
                                    {{ $movements->links('pagination::tailwind') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($show_detail_modal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-semibold leading-6 text-slate-900" id="modal-title">
                                    {{ __('Movement Details') }}
                                </h3>
                                <div class="mt-4 space-y-4">
                                    @if ($selected_movement)
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="text-sm font-medium text-slate-500">{{ __('Date & Time') }}</label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    {{ optional($selected_movement->moved_at)->format('Y-m-d H:i:s') }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500">{{ __('Branch') }}</label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    {{ $selected_movement->branch?->name ?? '-' }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500">{{ __('Product') }}</label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    {{ $selected_movement->product?->name ?? '-' }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500">{{ __('Movement Type') }}</label>
                                                <div class="mt-1 text-sm font-semibold {{ $selected_movement->movement_type === 'IN' ? 'text-green-700' : 'text-red-700' }}">
                                                    {{ $selected_movement->movement_type }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500">{{ __('Quantity') }}</label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    {{ (int) $selected_movement->quantity }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500">{{ __('User') }}</label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    {{ $selected_movement->user?->name ?? '-' }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500">{{ __('Before Stock') }}</label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    {{ (int) $selected_movement->before_stock }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500">{{ __('After Stock') }}</label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    {{ (int) $selected_movement->after_stock }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500">{{ __('Unit Cost') }}</label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    {{ $selected_movement->unit_cost !== null ? number_format((float) $selected_movement->unit_cost, 2) : '-' }}
                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500">{{ __('Unit Price') }}</label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    {{ $selected_movement->unit_price !== null ? number_format((float) $selected_movement->unit_price, 2) : '-' }}
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-slate-500">{{ __('Reference') }}</label>
                                            <div class="mt-1 text-sm text-slate-900">
                                                @if ($selected_movement->stock_in_receipt_id)
                                                    {{ __('Stock In Receipt') }} #{{ $selected_movement->stock_in_receipt_id }}
                                                @elseif ($selected_movement->sales_receipt_id)
                                                    {{ __('Sales Receipt') }} #{{ $selected_movement->sales_receipt_id }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="closeDetailModal" class="ui-btn-primary">
                            {{ __('Close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif
</div>
</div>
