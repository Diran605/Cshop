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
                                </tr>
                            @endforeach

                            @if ($movements->isEmpty())
                                <tr>
                                    <td colspan="11" class="ui-table-empty">{{ __('No movements found.') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
