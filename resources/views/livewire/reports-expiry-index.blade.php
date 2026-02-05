<div class="ui-page">
    <div class="ui-page-container print-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Expiry Report') }}</h2>
            <div class="ui-page-subtitle">{{ __('Expired and near-expiry batches (loss risk).') }}</div>
        </div>

        <style>
            @media print {
                .no-print {
                    display: none !important;
                }

                .print-container {
                    max-width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }
            }
        </style>

        <div class="ui-card no-print">
            <div class="ui-card-body">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="inline-flex items-center gap-2">
                        <a href="{{ route('reports.index') }}" class="ui-btn-secondary">{{ __('Sales') }}</a>
                        <a href="{{ route('reports.profit') }}" class="ui-btn-secondary">{{ __('Profit') }}</a>
                        <a href="{{ route('reports.stock') }}" class="ui-btn-secondary">{{ __('Stock') }}</a>
                        <a href="{{ route('reports.expenses') }}" class="ui-btn-secondary">{{ __('Expenses') }}</a>
                        <a href="{{ route('reports.expiry') }}" class="ui-btn-primary">{{ __('Expiry') }}</a>
                    </div>
                    <button type="button" onclick="window.print()" class="ui-btn-primary">{{ __('Print') }}</button>
                </div>

                <div class="mt-4 grid grid-cols-1 lg:grid-cols-4 gap-4">
                    <div>
                        <label class="ui-label">{{ __('Branch') }}</label>
                        @if ($isSuperAdmin)
                            <select wire:model="branch_id" class="mt-1 ui-select">
                                <option value="0">{{ __('All') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        @else
                            <div class="mt-1 rounded-lg border border-slate-300/80 bg-white/60 px-3 py-2 text-sm text-slate-700">
                                {{ $branches->first()?->name ?? '-' }}
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Near-expiry days') }}</label>
                        <input type="number" min="0" wire:model="days_ahead" class="mt-1 ui-input" />
                    </div>

                    <div class="lg:col-span-2">
                        <label class="ui-label">{{ __('Search') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="mt-1 ui-input" placeholder="Search product name..." />
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-slate-500">{{ __('Expired Batches') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format((int) $expiredCount) }}</div>
                </div>
            </div>
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-slate-500">{{ __('Estimated Expired Loss') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format((float) $expiredLoss, 2) }}</div>
                </div>
            </div>
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-slate-500">{{ __('Near-expiry Batches') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format((int) $nearExpiryCount) }}</div>
                </div>
            </div>
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="text-sm text-slate-500">{{ __('Near-expiry Value') }}</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-900">{{ number_format((float) $nearExpiryValue, 2) }}</div>
                </div>
            </div>
        </div>

        <div class="mt-6 ui-card">
            <div class="ui-card-body">
                <h3 class="ui-card-title">{{ __('Expired Products (Remaining Stock)') }}</h3>

                <div class="mt-4 overflow-x-auto">
                    <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Batch') }}</th>
                                    <th>{{ __('Expiry') }}</th>
                                    <th class="text-right">{{ __('Remaining') }}</th>
                                    <th class="text-right">{{ __('Unit Cost') }}</th>
                                    <th class="text-right">{{ __('Estimated Loss') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($expiredRows as $row)
                                    @php($loss = (float) ($row->cost_price ?? 0) * (int) ($row->remaining_quantity ?? 0))
                                    <tr>
                                        <td class="font-medium text-slate-900">{{ $row->product_name }}</td>
                                        <td>{{ $row->batch_ref_no ?: '-' }}</td>
                                        <td>{{ $row->expiry_date }}</td>
                                        <td class="text-right">{{ number_format((int) $row->remaining_quantity) }}</td>
                                        <td class="text-right">{{ number_format((float) ($row->cost_price ?? 0), 2) }}</td>
                                        <td class="text-right">{{ number_format((float) $loss, 2) }}</td>
                                    </tr>
                                @endforeach

                                @if ($expiredRows->isEmpty())
                                    <tr>
                                        <td colspan="6" class="ui-table-empty">{{ __('No expired batches found.') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-6 ui-card">
            <div class="ui-card-body">
                <h3 class="ui-card-title">{{ __('Near-expiry Products (Remaining Stock)') }}</h3>

                <div class="mt-4 overflow-x-auto">
                    <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Batch') }}</th>
                                    <th>{{ __('Expiry') }}</th>
                                    <th class="text-right">{{ __('Remaining') }}</th>
                                    <th class="text-right">{{ __('Unit Cost') }}</th>
                                    <th class="text-right">{{ __('Estimated Value') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($nearExpiryRows as $row)
                                    @php($value = (float) ($row->cost_price ?? 0) * (int) ($row->remaining_quantity ?? 0))
                                    <tr>
                                        <td class="font-medium text-slate-900">{{ $row->product_name }}</td>
                                        <td>{{ $row->batch_ref_no ?: '-' }}</td>
                                        <td>{{ $row->expiry_date }}</td>
                                        <td class="text-right">{{ number_format((int) $row->remaining_quantity) }}</td>
                                        <td class="text-right">{{ number_format((float) ($row->cost_price ?? 0), 2) }}</td>
                                        <td class="text-right">{{ number_format((float) $value, 2) }}</td>
                                    </tr>
                                @endforeach

                                @if ($nearExpiryRows->isEmpty())
                                    <tr>
                                        <td colspan="6" class="ui-table-empty">{{ __('No near-expiry batches found.') }}</td>
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
