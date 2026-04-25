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
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($batches as $batch)
                                @php
                                $status = $batch->voided_at ? 'voided' : ($batch->remaining_quantity <= 0 ? 'depleted' : (!$batch->expiry_date ? 'active' : (now()->parse($batch->expiry_date)->isPast() ? 'expired' : (now()->parse($batch->expiry_date)->diffInDays(now()) <= 30 ? 'expiring' : 'active' ))));
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
                                        <td>
                                            @can('clearance.send')
                                            @if ($batch->remaining_quantity > 0 && !$batch->voided_at)
                                            <button type="button"
                                                wire:click="openClearanceModal({{ $batch->id }}, {{ $batch->remaining_quantity }})"
                                                class="inline-flex items-center px-3 py-1 text-sm font-medium rounded-md bg-amber-50 text-amber-700 hover:bg-amber-100 transition-colors">
                                                {{ __('Send to Clearance') }}
                                            </button>
                                            @endif
                                            @endcan
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

    {{-- Send to Clearance Modal --}}
    @if ($show_clearance_modal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 max-h-96 overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-200 p-4">
                <h3 class="text-lg font-semibold text-slate-900">Send to Clearance</h3>
                <button type="button" wire:click="closeClearanceModal()" class="text-slate-500 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="p-4 space-y-4">
                @if ($errors->has('clearance'))
                <div class="bg-red-50 border border-red-200 rounded-md p-3">
                    <p class="text-sm text-red-700">{{ $errors->first('clearance') }}</p>
                </div>
                @endif

                {{-- Available Quantity Info --}}
                <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
                    <p class="text-sm text-blue-700">
                        <span class="font-semibold">Available Quantity:</span> {{ $selected_remaining_qty }} units
                    </p>
                </div>

                {{-- Action Choice --}}
                <div class="space-y-2">
                    <label class="text-sm font-medium text-slate-700">Choose Action:</label>
                    <div class="space-y-2">
                        <label class="flex items-center p-3 border border-slate-200 rounded-md cursor-pointer hover:bg-slate-50" wire:click="$set('clearance_action', 'partial')">
                            <input type="radio" name="clearance_action" value="partial" wire:model="clearance_action" class="mr-2" />
                            <span class="text-sm font-medium">Allocate Partial</span>
                        </label>

                        <label class="flex items-center p-3 border border-slate-200 rounded-md cursor-pointer hover:bg-slate-50" wire:click="$set('clearance_action', 'entire')">
                            <input type="radio" name="clearance_action" value="entire" wire:model="clearance_action" class="mr-2" />
                            <span class="text-sm font-medium">Move Entire Batch</span>
                        </label>
                    </div>
                </div>

                {{-- Partial Quantity Input --}}
                @if ($clearance_action === 'partial')
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Quantity to Allocate</label>
                    <input type="number" min="1" max="{{ $selected_remaining_qty }}" wire:model.live="clearance_partial_qty" class="ui-input" placeholder="Enter quantity" />
                    @if ($clearance_partial_qty > 0)
                    <p class="text-xs text-slate-600 mt-1">
                        Remaining after: <span class="font-semibold">{{ ($selected_remaining_qty ?? 0) - $clearance_partial_qty }}</span> units
                    </p>
                    @endif
                </div>
                @else
                <div class="bg-amber-50 border border-amber-200 rounded-md p-3">
                    <p class="text-sm text-amber-700">
                        All <span class="font-semibold">{{ $selected_remaining_qty }}</span> units will be allocated to clearance.
                    </p>
                </div>
                @endif

                {{-- Reason --}}
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Reason</label>
                    <textarea wire:model.live="clearance_reason" class="ui-input min-h-20 resize-none" placeholder="Why is this being sent to clearance?"></textarea>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 border-t border-slate-200 p-4">
                <button type="button" wire:click="closeClearanceModal()" class="ui-btn-secondary">Cancel</button>
                <button type="button" wire:click="sendToClearance()" wire:loading.attr="disabled" class="ui-btn-primary">
                    <span wire:loading.remove>Send to Clearance</span>
                    <span wire:loading><svg class="inline-block w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg> Processing...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>