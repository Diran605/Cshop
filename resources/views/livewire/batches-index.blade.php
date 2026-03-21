<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-bold tracking-wider uppercase text-purple-600 mb-1">Inventory</div>
                    <h1 class="text-2xl font-bold text-slate-900">Batch Management</h1>
                </div>
            </div>
        </div>

        @if (session('status'))
            <div class="ui-alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="ui-alert-danger">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="ui-card mb-6">
            <div class="ui-card-body">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @if ($isSuperAdmin)
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Branch</label>
                            <select wire:model.live="branch_id" class="ui-select">
                                <option value="0">{{ __('All Branches') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Product</label>
                        <select wire:model.live="product_id" class="ui-select">
                            <option value="0">{{ __('All Products') }}</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Status</label>
                        <select wire:model.live="status_filter" class="ui-select">
                            <option value="all">{{ __('All') }}</option>
                            <option value="active">{{ __('Active') }}</option>
                            <option value="expiring">{{ __('Expiring Soon') }}</option>
                            <option value="expired">{{ __('Expired') }}</option>
                            <option value="voided">{{ __('Voided') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Search</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="ui-input" placeholder="Product, batch, receipt..." />
                    </div>
                </div>
            </div>
        </div>

        {{-- Batches Table --}}
        <div class="ui-card">
            <div class="ui-card-body">
                <div class="overflow-x-auto">
                    <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Category') }}</th>
                                    <th>{{ __('Batch Ref') }}</th>
                                    <th>{{ __('Original Qty') }}</th>
                                    <th>{{ __('Remaining') }}</th>
                                    <th>{{ __('Cost Price') }}</th>
                                    <th>{{ __('Expiry Date') }}</th>
                                    <th>{{ __('Receipt No') }}</th>
                                    <th>{{ __('Received') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($batches as $batch)
                                    @php
                                        $status = $batch->voided_at ? 'voided' : ($batch->remaining_quantity <= 0 ? 'depleted' : (!$batch->expiry_date ? 'active' : (now()->parse($batch->expiry_date)->isPast() ? 'expired' : (now()->parse($batch->expiry_date)->diffInDays(now()) <= 30 ? 'expiring' : 'active'))));
                                    @endphp
                                    <tr>
                                        <td class="font-medium">{{ $batch->product_name }}</td>
                                        <td>{{ $batch->category_name ?? '-' }}</td>
                                        <td>{{ $batch->batch_ref_no ?? '-' }}</td>
                                        <td>{{ $batch->original_quantity }}</td>
                                        <td>
                                            <span class="{{ $batch->remaining_quantity <= 0 ? 'text-red-600' : '' }}">
                                                {{ $batch->remaining_quantity }}
                                            </span>
                                        </td>
                                        <td>{{ $batch->cost_price ? number_format((float) $batch->cost_price, 2) : '-' }}</td>
                                        <td>
                                            @if ($batch->expiry_date)
                                                <span class="{{ now()->parse($batch->expiry_date)->isPast() ? 'text-red-600' : (now()->parse($batch->expiry_date)->diffInDays(now()) <= 30 ? 'text-orange-600' : '') }}">
                                                    {{ \Carbon\Carbon::parse($batch->expiry_date)->format('Y-m-d') }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $batch->receipt_no }}</td>
                                        <td>{{ $batch->received_at ? \Carbon\Carbon::parse($batch->received_at)->format('Y-m-d') : '-' }}</td>
                                        <td>
                                            @switch($status)
                                                @case('voided')
                                                    <span class="ui-badge-danger">{{ __('Voided') }}</span>
                                                    @break
                                                @case('depleted')
                                                    <span class="ui-badge-info">{{ __('Depleted') }}</span>
                                                    @break
                                                @case('expired')
                                                    <span class="ui-badge-danger">{{ __('Expired') }}</span>
                                                    @break
                                                @case('expiring')
                                                    <span class="ui-badge-warning">{{ __('Expiring') }}</span>
                                                    @break
                                                @default
                                                    <span class="ui-badge-success">{{ __('Active') }}</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="ui-table-empty">{{ __('No batches found.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (method_exists($batches, 'hasPages') && $batches->hasPages())
                        <div class="mt-4 flex items-center justify-between">
                            <div class="text-sm text-slate-600">
                                {{ __('Showing') }} {{ $batches->firstItem() }} {{ __('to') }} {{ $batches->lastItem() }} {{ __('of') }} {{ $batches->total() }} {{ __('results') }}
                            </div>
                            {{ $batches->links('pagination::tailwind') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
