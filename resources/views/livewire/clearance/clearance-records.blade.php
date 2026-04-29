<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <div class="text-xs font-bold tracking-wider uppercase text-amber-600 mb-1">Clearance</div>
                    <h1 class="text-2xl font-bold text-slate-900">Clearance Records</h1>
                    <div class="text-sm text-slate-500 mt-1">{{ __('View, edit, and manage clearance item records') }}</div>
                </div>
                <a href="{{ route('clearance.index') }}" class="ui-btn-secondary">
                    {{ __('Clearance Manager') }}
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="ui-alert-success">{{ session('status') }}</div>
        @endif

        @if (session('error'))
            <div class="ui-alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="ui-card">
                <div class="ui-card-body py-3">
                    <div class="text-sm text-slate-500">{{ __('Total Records') }}</div>
                    <div class="text-xl font-semibold text-slate-900">{{ $stats['total'] }}</div>
                </div>
            </div>
            <div class="ui-card">
                <div class="ui-card-body py-3">
                    <div class="text-sm text-slate-500">{{ __('Pending') }}</div>
                    <div class="text-xl font-semibold text-amber-600">{{ $stats['pending'] }}</div>
                </div>
            </div>
            <div class="ui-card">
                <div class="ui-card-body py-3">
                    <div class="text-sm text-slate-500">{{ __('Actioned') }}</div>
                    <div class="text-xl font-semibold text-emerald-600">{{ $stats['actioned'] }}</div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="ui-card mb-6">
            <div class="ui-card-body">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @if ($isSuperAdmin)
                        <div>
                            <label class="ui-label">{{ __('Branch') }}</label>
                            <select wire:model.live="filter_branch_id" class="mt-1 ui-select">
                                <option value="0">{{ __('All Branches') }}</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="ui-label">{{ __('Status') }}</label>
                        <select wire:model.live="filter_status" class="mt-1 ui-select">
                            <option value="all">{{ __('All Statuses') }}</option>
                            <option value="approaching">{{ __('Approaching') }}</option>
                            <option value="urgent">{{ __('Urgent') }}</option>
                            <option value="critical">{{ __('Critical') }}</option>
                            <option value="expired">{{ __('Expired') }}</option>
                            <option value="actioned">{{ __('Actioned') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="ui-label">{{ __('Action Type') }}</label>
                        <select wire:model.live="filter_action_type" class="mt-1 ui-select">
                            <option value="all">{{ __('All Types') }}</option>
                            <option value="discount">{{ __('Discount') }}</option>
                            <option value="donate">{{ __('Donate') }}</option>
                            <option value="dispose">{{ __('Dispose') }}</option>
                            <option value="declined">{{ __('Declined') }}</option>
                            <option value="rejected">{{ __('Rejected') }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="ui-label">{{ __('Search') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="mt-1 ui-input" placeholder="{{ __('Search product...') }}" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="ui-card">
            <div class="ui-card-body p-0">
                <div class="overflow-x-auto">
                    <table class="ui-table min-w-full">
                        <thead>
                            <tr>
                                @if ($isSuperAdmin)
                                    <th>{{ __('Branch') }}</th>
                                @endif
                                <th>{{ __('Product') }}</th>
                                <th class="text-center">{{ __('Status') }}</th>
                                <th class="text-right">{{ __('Qty') }}</th>
                                <th class="text-right">{{ __('Orig Price') }}</th>
                                <th class="text-right">{{ __('Clr Price') }}</th>
                                <th class="text-center">{{ __('Expiry') }}</th>
                                <th class="text-center">{{ __('Action') }}</th>
                                <th class="text-center">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($records as $item)
                                <tr wire:key="clearance-{{ $item->id }}">
                                    @if ($isSuperAdmin)
                                        <td class="whitespace-nowrap">{{ $item->branch?->name ?? '-' }}</td>
                                    @endif
                                    <td class="whitespace-nowrap font-medium">{{ $item->product?->name ?? '-' }}</td>
                                    <td class="text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ match($item->status) {
                                            'approaching' => 'bg-yellow-100 text-yellow-800',
                                            'urgent' => 'bg-orange-100 text-orange-800',
                                            'critical' => 'bg-red-100 text-red-800',
                                            'expired' => 'bg-gray-100 text-gray-800',
                                            'actioned' => 'bg-green-100 text-green-800',
                                            default => 'bg-gray-100 text-gray-800',
                                        } }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="text-right font-mono">{{ $item->quantity }}</td>
                                    <td class="text-right font-mono">{{ number_format($item->original_price, 2) }}</td>
                                    <td class="text-right font-mono">
                                        {{ $item->clearance_price ? number_format($item->clearance_price, 2) : '-' }}
                                    </td>
                                    <td class="text-center whitespace-nowrap">
                                        {{ $item->expiry_date?->format('Y-m-d') ?? '-' }}
                                        @if($item->days_to_expiry !== null)
                                            <span class="text-xs text-slate-500">({{ $item->days_to_expiry }}d)</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->action_type)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ match($item->action_type) {
                                                'discount' => 'bg-blue-100 text-blue-800',
                                                'donate' => 'bg-purple-100 text-purple-800',
                                                'dispose' => 'bg-red-100 text-red-800',
                                                'declined' => 'bg-yellow-100 text-yellow-800',
                                                'rejected' => 'bg-gray-100 text-gray-800',
                                                default => 'bg-gray-100 text-gray-800',
                                            } }}">
                                                {{ ucfirst($item->action_type) }}
                                            </span>
                                            @if($item->actionedBy)
                                                <div class="text-xs text-slate-400 mt-0.5">
                                                    {{ $item->actionedBy->name }}
                                                    @if($item->actioned_at)
                                                        · {{ $item->actioned_at->format('d M H:i') }}
                                                    @endif
                                                </div>
                                            @endif
                                        @else
                                            <span class="text-slate-400">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            @can('clearance.records.view')
                                                <button type="button" wire:click="openViewModal({{ $item->id }})" class="ui-btn-link text-xs">
                                                    {{ __('View') }}
                                                </button>
                                            @endcan
                                            @if($item->status !== 'actioned')
                                                @can('clearance.records.edit')
                                                    <button type="button" wire:click="openEditModal({{ $item->id }})" class="ui-btn-link text-xs">
                                                        {{ __('Edit') }}
                                                    </button>
                                                @endcan
                                                @can('clearance.records.delete')
                                                    <button type="button" wire:click="delete({{ $item->id }})" class="ui-btn-link text-xs text-red-600" onclick="return confirm('Delete this record?')">
                                                        {{ __('Delete') }}
                                                    </button>
                                                @endcan
                                            @endif
                                            @if($item->status === 'actioned')
                                                @if($item->action_type === 'discount')
                                                    @can('clearance.dispose')
                                                        <button type="button" wire:click="openDisposeModal({{ $item->id }})" class="btn-xs bg-red-100 text-red-700 hover:bg-red-200">
                                                            🗑️ {{ __('Dispose') }}
                                                        </button>
                                                    @endcan
                                                @endif
                                                @can('clearance.reverse')
                                                    <button type="button" wire:click="openReversalModal({{ $item->id }})" class="btn-xs bg-amber-100 text-amber-700 hover:bg-amber-200">
                                                        🔄 {{ __('Reverse') }}
                                                    </button>
                                                @endcan
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $isSuperAdmin ? 9 : 8 }}" class="ui-table-empty">
                                        {{ __('No clearance records found.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($records->hasPages())
                    <div class="p-4 border-t border-slate-200">
                        {{ $records->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- View Modal --}}
    @if ($show_view_modal && $viewItem)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeViewModal" data-modal-overlay></div>
            <div class="relative w-full max-w-lg ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="font-semibold text-slate-900">{{ __('Clearance Record Details') }}</div>
                </div>

                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-slate-500 uppercase">{{ __('Product') }}</label>
                            <div class="mt-1 font-medium">{{ $viewItem->product?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-500 uppercase">{{ __('Branch') }}</label>
                            <div class="mt-1">{{ $viewItem->branch?->name ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="text-xs font-medium text-slate-500 uppercase">{{ __('Quantity') }}</label>
                            <div class="mt-1 font-mono">{{ $viewItem->quantity }}</div>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-500 uppercase">{{ __('Original Price') }}</label>
                            <div class="mt-1 font-mono">{{ number_format($viewItem->original_price, 2) }}</div>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-500 uppercase">{{ __('Clearance Price') }}</label>
                            <div class="mt-1 font-mono">{{ $viewItem->clearance_price ? number_format($viewItem->clearance_price, 2) : '-' }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-slate-500 uppercase">{{ __('Expiry Date') }}</label>
                            <div class="mt-1">{{ $viewItem->expiry_date?->format('Y-m-d') ?? '-' }}</div>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-500 uppercase">{{ __('Days to Expiry') }}</label>
                            <div class="mt-1">{{ $viewItem->days_to_expiry ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-slate-500 uppercase">{{ __('Status') }}</label>
                            <div class="mt-1">{{ ucfirst($viewItem->status) }}</div>
                        </div>
                        <div>
                            <label class="text-xs font-medium text-slate-500 uppercase">{{ __('Action Type') }}</label>
                            <div class="mt-1">{{ $viewItem->action_type ? ucfirst($viewItem->action_type) : '-' }}</div>
                        </div>
                    </div>

                    @if($viewItem->notes)
                        <div>
                            <label class="text-xs font-medium text-slate-500 uppercase">{{ __('Notes') }}</label>
                            <div class="mt-1 text-sm text-slate-700">{{ $viewItem->notes }}</div>
                        </div>
                    @endif

                    @if($viewItem->actionedBy)
                        <div class="text-xs text-slate-500">
                            {{ __('Actioned by') }}: {{ $viewItem->actionedBy->name }} @if($viewItem->actioned_at) on {{ $viewItem->actioned_at->format('Y-m-d H:i') }}@endif
                        </div>
                    @endif
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-end">
                    <button type="button" wire:click="closeViewModal" class="ui-btn-secondary" data-modal-close>
                        {{ __('Close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Modal --}}
    @if ($show_edit_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeEditModal" data-modal-overlay></div>
            <div class="relative w-full max-w-md ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="font-semibold text-slate-900">{{ __('Edit Clearance Record') }}</div>
                </div>

                <div class="p-4 space-y-4">
                    <div>
                        <label class="ui-label">{{ __('Quantity') }}</label>
                        <input type="number" wire:model.defer="edit_quantity" class="mt-1 ui-input" min="1" />
                        @error('edit_quantity') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Original Price') }}</label>
                        <input type="number" step="0.01" wire:model.defer="edit_original_price" class="mt-1 ui-input" />
                        @error('edit_original_price') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Clearance Price') }}</label>
                        <input type="number" step="0.01" wire:model.defer="edit_clearance_price" class="mt-1 ui-input" />
                        @error('edit_clearance_price') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Notes') }}</label>
                        <textarea wire:model.defer="edit_notes" class="mt-1 ui-input" rows="2"></textarea>
                        @error('edit_notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-end gap-3">
                    <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" wire:click="saveEdit" class="ui-btn-primary">
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Reversal Modal --}}
    @if ($show_reversal_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeReversalModal" data-modal-overlay></div>
            <div class="relative w-full max-w-md ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="font-semibold text-slate-900">🔄 {{ __('Reverse Clearance Action') }}</div>
                    <p class="text-sm text-slate-500 mt-1">{{ __('This will undo the clearance action and optionally restore stock.') }}</p>
                </div>

                <div class="p-4 space-y-4">
                    <div>
                        <label class="ui-label">{{ __('Reason for Reversal') }} *</label>
                        <textarea wire:model="reversal_reason" class="mt-1 ui-input" rows="3" placeholder="{{ __('Why is this clearance action being reversed?') }}"></textarea>
                        @error('reversal_reason') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="reversal_restore_to_stock" id="restore_stock" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="restore_stock" class="text-sm text-slate-700">{{ __('Restore quantity back to stock') }}</label>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-end gap-3">
                    <button type="button" wire:click="closeReversalModal" class="ui-btn-secondary" data-modal-close>
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" wire:click="reverseAction" class="ui-btn-primary bg-amber-600 hover:bg-amber-700">
                        🔄 {{ __('Reverse Action') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Disposal Modal --}}
    @if ($show_dispose_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeDisposeModal" data-modal-overlay></div>
            <div class="relative w-full max-w-md ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="font-semibold text-slate-900">🗑️ {{ __('Dispose Remaining Clearance Stock') }}</div>
                    <p class="text-sm text-slate-500 mt-1">{{ __('This will remove items from inventory and record them as a loss.') }}</p>
                </div>

                <div class="p-4 space-y-4">
                    <div>
                        <label class="ui-label">{{ __('Quantity to Dispose') }} ({{ __('Max') }}: {{ $dispose_max_quantity }})</label>
                        <input type="number" wire:model="dispose_quantity" class="mt-1 ui-input" min="1" max="{{ $dispose_max_quantity }}" />
                        @error('dispose_quantity') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Disposal Reason') }}</label>
                        <select wire:model="dispose_reason" class="mt-1 ui-select">
                            @foreach(\App\Models\Disposal::getReasons() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('dispose_reason') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Disposal Method') }}</label>
                        <select wire:model="dispose_method" class="mt-1 ui-select">
                            @foreach(\App\Models\Disposal::getMethods() as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('dispose_method') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Notes') }}</label>
                        <textarea wire:model="dispose_notes" class="mt-1 ui-input" rows="2" placeholder="{{ __('Additional details about the disposal...') }}"></textarea>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-end gap-3">
                    <button type="button" wire:click="closeDisposeModal" class="ui-btn-secondary" data-modal-close>
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" wire:click="recordDisposal" class="ui-btn-primary bg-red-600 hover:bg-red-700">
                        🗑️ {{ __('Confirm Disposal') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
