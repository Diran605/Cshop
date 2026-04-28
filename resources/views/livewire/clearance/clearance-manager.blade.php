<div class="ui-page">
    <div class="ui-page-container">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('Clearance Manager') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ __('Manage products approaching expiry with discounts, donations, or disposals') }}</p>
            </div>
            <div class="flex items-center gap-3">
                @if ($this->isSuperAdmin)
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-slate-600">{{ __('Branch:') }}</label>
                        <select wire:model.live="filter_branch_id" class="ui-input w-48">
                            <option value="0">{{ __('All Branches') }}</option>
                            @foreach ($this->branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <a href="{{ route('clearance.rules') }}" class="ui-btn-secondary">
                    {{ __('Discount Rules') }}
                </a>
                <a href="{{ route('clearance.reports') }}" class="ui-btn-secondary">
                    {{ __('Reports') }}
                </a>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="ui-kpi-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">{{ __('Items Pending') }}</div>
                        <div class="text-xl font-bold text-slate-900">{{ $this->stats['total_pending'] }}</div>
                    </div>
                </div>
            </div>

            <div class="ui-kpi-card bg-red-50 border-red-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-red-600">{{ __('Value at Risk') }}</div>
                        <div class="text-xl font-bold text-red-700">XAF {{ number_format($this->stats['total_value_at_risk'], 0, ',', ' ') }}</div>
                    </div>
                </div>
            </div>

            <div class="ui-kpi-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500">{{ __('Urgent/Critical') }}</div>
                        <div class="text-xl font-bold text-amber-600">{{ $this->stats['by_status']['urgent'] + $this->stats['by_status']['critical'] }}</div>
                    </div>
                </div>
            </div>

            <div class="ui-kpi-card bg-gray-50 border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-gray-600">{{ __('Expired') }}</div>
                        <div class="text-xl font-bold text-gray-700">{{ $this->stats['by_status']['expired'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <div class="ui-card mb-6">
            <div class="ui-card-body">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Search Product') }}</label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="ui-input" placeholder="{{ __('Search by product name...') }}">
                    </div>
                    <div class="w-40">
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Status') }}</label>
                        <select wire:model.live="filter_status" class="ui-input">
                            <option value="all">{{ __('All Status') }}</option>
                            <option value="approaching">{{ __('Approaching') }}</option>
                            <option value="urgent">{{ __('Urgent') }}</option>
                            <option value="critical">{{ __('Critical') }}</option>
                            <option value="expired">{{ __('Expired') }}</option>
                            <option value="actioned">{{ __('Actioned') }}</option>
                        </select>
                    </div>
                    <div class="w-40">
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Action') }}</label>
                        <select wire:model.live="filter_action" class="ui-input">
                            <option value="pending">{{ __('Pending') }}</option>
                            <option value="actioned">{{ __('Actioned') }}</option>
                            <option value="all">{{ __('All') }}</option>
                        </select>
                    </div>
                    <div class="w-48">
                        <label class="block text-xs font-medium text-slate-600 mb-1">{{ __('Approval') }}</label>
                        <select wire:model.live="filter_approval" class="ui-input">
                            <option value="all">{{ __('All Approvals') }}</option>
                            <option value="pending_approval">{{ __('⏳ Pending Review') }}</option>
                            <option value="approved">{{ __('✅ Approved') }}</option>
                            <option value="manual">{{ __('👤 Manual') }}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items Table --}}
        <div class="ui-card">
            <div class="ui-card-body p-0">
                @if ($this->clearanceItems->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Product') }}</th>
                                    <th>{{ __('Expiry') }}</th>
                                    <th>{{ __('Days Left') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Approval') }}</th>
                                    <th class="text-right">{{ __('Qty') }}</th>
                                    <th class="text-right">{{ __('Original Price') }}</th>
                                    <th class="text-right">{{ __('Suggested Discount') }}</th>
                                    <th class="text-right">{{ __('Clearance Price') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->clearanceItems as $item)
                                    <tr class="{{ $item->status === 'expired' ? 'bg-gray-50' : '' }}">
                                        <td>
                                            <div class="font-medium text-slate-900">{{ $item->product?->name ?? '-' }}</div>
                                            @if ($item->action_type)
                                                <span class="text-xs text-slate-500">{{ ucfirst($item->action_type) }} by {{ $item->actionedBy?->name }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->expiry_date->format('d M Y') }}</td>
                                        <td>
                                            @php $days = $item->days_to_expiry; @endphp
                                            <span class="{{ $days < 0 ? 'text-gray-500' : ($days <= 3 ? 'text-red-600 font-bold' : ($days <= 7 ? 'text-orange-600' : 'text-amber-600')) }}">
                                                {{ $days < 0 ? __('Expired') : $days }} {{ $days >= 0 ? __('days') : __('ago') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $this->getStatusBadgeClass($item->status) }}">
                                                {{ ucfirst($item->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            @php $as = $item->approval_status ?? 'manual'; @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ $as === 'approved' ? 'bg-green-100 text-green-700' : '' }}
                                                {{ $as === 'auto_suggested' || $as === 'pending_approval' ? 'bg-amber-100 text-amber-700' : '' }}
                                                {{ $as === 'declined' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                {{ $as === 'rejected' ? 'bg-red-100 text-red-700' : '' }}
                                                {{ $as === 'manual' ? 'bg-blue-100 text-blue-700' : '' }}
                                                {{ $as === 'reversed' ? 'bg-purple-100 text-purple-700' : '' }}
                                            ">
                                                {{ $as === 'auto_suggested' ? '🤖 Auto-Suggested' : '' }}
                                                {{ $as === 'pending_approval' ? '⏳ Pending' : '' }}
                                                {{ $as === 'approved' ? '✅ Approved' : '' }}
                                                {{ $as === 'declined' ? '⏸ Declined' : '' }}
                                                {{ $as === 'rejected' ? '❌ Rejected' : '' }}
                                                {{ $as === 'manual' ? '👤 Manual' : '' }}
                                                {{ $as === 'reversed' ? '🔄 Reversed' : '' }}
                                            </span>
                                        </td>
                                        <td class="text-right font-medium">{{ $item->quantity }}</td>
                                        <td class="text-right">XAF {{ number_format($item->original_price, 0, ',', ' ') }}</td>
                                        <td class="text-right">
                                            @if ($item->suggested_discount_pct > 0)
                                                <span class="text-green-600">{{ $item->suggested_discount_pct }}%</span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if ($item->clearance_price)
                                                <span class="text-green-600 font-medium">XAF {{ number_format($item->clearance_price, 0, ',', ' ') }}</span>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex flex-wrap items-center gap-1">
                                                {{-- Approval actions for pending items --}}
                                                @if (in_array($as, ['auto_suggested', 'pending_approval']))
                                                    @can('clearance.approve')
                                                        <button wire:click="openApprovalModal({{ $item->id }}, 'approve')" class="btn-xs bg-green-100 text-green-700 hover:bg-green-200" title="{{ __('Approve') }}">
                                                            ✅ {{ __('Approve') }}
                                                        </button>
                                                        <button wire:click="openApprovalModal({{ $item->id }}, 'reject')" class="btn-xs bg-red-100 text-red-700 hover:bg-red-200" title="{{ __('Reject') }}">
                                                            ❌ {{ __('Reject') }}
                                                        </button>
                                                    @endcan
                                                @elseif ($item->status !== 'actioned' && in_array($as, ['approved', 'manual', 'reversed']))
                                                    {{-- Clearance actions for approved/manual items --}}
                                                    @can('clearance.discount')
                                                        <button wire:click="openDiscountModal({{ $item->id }})" class="btn-xs bg-green-100 text-green-700 hover:bg-green-200" title="{{ __('Discount') }}">
                                                            🏷️ {{ __('Discount') }}
                                                        </button>
                                                    @endcan
                                                    @can('clearance.donate')
                                                        <button wire:click="openDonateModal({{ $item->id }})" class="btn-xs bg-purple-100 text-purple-700 hover:bg-purple-200" title="{{ __('Donate') }}">
                                                            💜 {{ __('Donate') }}
                                                        </button>
                                                    @endcan
                                                    @can('clearance.dispose')
                                                        <button wire:click="openDisposeModal({{ $item->id }})" class="btn-xs bg-red-100 text-red-700 hover:bg-red-200" title="{{ __('Dispose') }}">
                                                            🗑️ {{ __('Dispose') }}
                                                        </button>
                                                    @endcan
                                                    @can('clearance.approve')
                                                        <button wire:click="openApprovalModal({{ $item->id }}, 'decline')" class="btn-xs bg-yellow-100 text-yellow-700 hover:bg-yellow-200" title="{{ __('Decline & Restore Stock') }}">
                                                            ⏸ {{ __('Decline') }}
                                                        </button>
                                                    @endcan
                                                @elseif ($item->status === 'actioned')
                                                    <span class="text-xs text-green-600">{{ __('Completed') }}</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-slate-200">
                        {{ $this->clearanceItems->links() }}
                    </div>
                @else
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-lg font-medium text-slate-600">{{ __('No Clearance Items') }}</p>
                        <p class="text-sm text-slate-500 mt-1">{{ __('All products are within safe expiry dates.') }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Discount Modal --}}
    @if ($show_discount_modal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="$toggle('show_discount_modal')">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <div class="p-6 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Apply Clearance Discount') }}</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-slate-50 rounded-lg p-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600">{{ __('Original Price') }}</span>
                            <span class="font-semibold text-slate-900">XAF {{ number_format($discount_original_price, 0, ',', ' ') }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Discount Percentage') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="range" wire:model.live="discount_percentage" min="0" max="100" step="5" class="flex-1">
                            <span class="w-16 text-center font-bold text-green-600">{{ $discount_percentage }}%</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Clearance Price') }}</label>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-500">XAF</span>
                            <input type="number" wire:model="discount_custom_price" class="ui-input flex-1" step="0.01">
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ __('Suggested: XAF') }} {{ number_format($discount_suggested_price, 0, ',', ' ') }}</p>
                    </div>

                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-green-600">{{ __('Customer Savings') }}</span>
                            <span class="font-semibold text-green-700">XAF {{ number_format($discount_original_price - $discount_custom_price, 0, ',', ' ') }}</span>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-200 flex justify-end gap-3">
                    <button wire:click="$toggle('show_discount_modal')" class="ui-btn-secondary">{{ __('Cancel') }}</button>
                    <button wire:click="applyDiscount" class="ui-btn-primary">{{ __('Apply Discount') }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Donate Modal --}}
    @if ($show_donate_modal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="$toggle('show_donate_modal')">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
                <div class="p-6 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Record Donation') }}</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Quantity to Donate') }}</label>
                        <input type="number" wire:model="donate_quantity" min="1" max="{{ $donate_max_quantity }}" class="ui-input">
                        <p class="text-xs text-slate-500 mt-1">{{ __('Available') }}: {{ $donate_max_quantity }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Organization Name') }} *</label>
                        <input type="text" wire:model="donate_organization" class="ui-input" placeholder="{{ __('e.g., Local Food Bank') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Contact Person/Phone') }}</label>
                        <input type="text" wire:model="donate_contact" class="ui-input">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Address') }}</label>
                        <textarea wire:model="donate_address" class="ui-input" rows="2"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Notes') }}</label>
                        <textarea wire:model="donate_notes" class="ui-input" rows="2"></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-200 flex justify-end gap-3">
                    <button wire:click="$toggle('show_donate_modal')" class="ui-btn-secondary">{{ __('Cancel') }}</button>
                    <button wire:click="recordDonation" class="ui-btn-primary bg-purple-600 hover:bg-purple-700">{{ __('Record Donation') }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Dispose Modal --}}
    @if ($show_dispose_modal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="$toggle('show_dispose_modal')">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
                <div class="p-6 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Record Disposal') }}</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Quantity to Dispose') }}</label>
                        <input type="number" wire:model="dispose_quantity" min="1" max="{{ $dispose_max_quantity }}" class="ui-input">
                        <p class="text-xs text-slate-500 mt-1">{{ __('Available') }}: {{ $dispose_max_quantity }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Disposal Reason') }} *</label>
                        <select wire:model="dispose_reason" class="ui-input">
                            <option value="">{{ __('Select reason...') }}</option>
                            @foreach (\App\Models\Disposal::getReasons() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Disposal Method') }}</label>
                        <select wire:model="dispose_method" class="ui-input">
                            <option value="">{{ __('Select method...') }}</option>
                            @foreach (\App\Models\Disposal::getMethods() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Notes') }}</label>
                        <textarea wire:model="dispose_notes" class="ui-input" rows="2"></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-200 flex justify-end gap-3">
                    <button wire:click="$toggle('show_dispose_modal')" class="ui-btn-secondary">{{ __('Cancel') }}</button>
                    <button wire:click="recordDisposal" class="ui-btn-primary bg-red-600 hover:bg-red-700">{{ __('Record Disposal') }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Approval Modal --}}
    @if ($show_approval_modal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeApprovalModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <div class="p-6 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">
                        @if ($approval_action === 'approve')
                            ✅ {{ __('Approve for Clearance') }}
                        @elseif ($approval_action === 'reject')
                            ❌ {{ __('Reject from Clearance') }}
                        @else
                            ⏸ {{ __('Decline & Restore Stock') }}
                        @endif
                    </h3>
                    <p class="text-sm text-slate-500 mt-1">
                        @if ($approval_action === 'approve')
                            {{ __('This item will be available for discount, donation, or disposal actions.') }}
                        @elseif ($approval_action === 'reject')
                            {{ __('This auto-suggested item will be removed from the clearance list. No stock changes.') }}
                        @else
                            {{ __('This item will be removed from clearance and stock will be restored back to the batch.') }}
                        @endif
                    </p>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Notes (optional)') }}</label>
                        <textarea wire:model="approval_notes" class="ui-input" rows="3" placeholder="{{ __('Add any notes about this decision...') }}"></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-200 flex justify-end gap-3">
                    <button wire:click="closeApprovalModal" class="ui-btn-secondary">{{ __('Cancel') }}</button>
                    <button wire:click="submitApproval" class="{{ $approval_action === 'approve' ? 'ui-btn-primary' : ($approval_action === 'reject' ? 'ui-btn-primary bg-red-600 hover:bg-red-700' : 'ui-btn-primary bg-yellow-500 hover:bg-yellow-600') }}">
                        @if ($approval_action === 'approve')
                            ✅ {{ __('Approve') }}
                        @elseif ($approval_action === 'reject')
                            ❌ {{ __('Reject') }}
                        @else
                            ⏸ {{ __('Decline & Restore') }}
                        @endif
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50" x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition>
            {{ session('success') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50" x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" x-transition>
            {{ session('error') }}
        </div>
    @endif
</div>
