<div class="ui-page">
    <div class="ui-page-container">
        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">{{ __('Clearance Discount Rules') }}</h1>
                <p class="text-sm text-slate-500 mt-1">{{ __('Configure automatic discount percentages based on days to expiry') }}</p>
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
                <a href="{{ route('clearance.index') }}" class="ui-btn-secondary">
                    {{ __('Back to Manager') }}
                </a>
                <button wire:click="openModal()" class="ui-btn-primary">
                    {{ __('Add Rule') }}
                </button>
            </div>
        </div>

        {{-- Info Card --}}
        <div class="ui-card mb-6 bg-blue-50 border-blue-200">
            <div class="ui-card-body">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-900">{{ __('How Discount Rules Work') }}</h4>
                        <p class="text-sm text-blue-700 mt-1">
                            {{ __('When products approach expiry, the system automatically suggests discounts based on these rules. Products within the days range will get the corresponding discount percentage.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rules Table --}}
        <div class="ui-card">
            <div class="ui-card-body p-0">
                @if ($this->discountRules->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    @if ($this->isSuperAdmin)
                                        <th class="whitespace-nowrap">{{ __('Branch') }}</th>
                                    @endif
                                    <th class="whitespace-nowrap">{{ __('Days to Expiry') }}</th>
                                    <th class="whitespace-nowrap">{{ __('Status Label') }}</th>
                                    <th class="whitespace-nowrap text-right">{{ __('Discount %') }}</th>
                                    <th class="whitespace-nowrap">{{ __('Scope') }}</th>
                                    <th class="whitespace-nowrap text-center">{{ __('Active') }}</th>
                                    <th class="whitespace-nowrap text-center">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->discountRules as $rule)
                                    <tr class="{{ ! $rule->is_active ? 'bg-gray-50' : '' }}">
                                        @if ($this->isSuperAdmin)
                                            <td class="whitespace-nowrap text-slate-600">{{ $rule->branch?->name ?? __('Global') }}</td>
                                        @endif
                                        <td class="whitespace-nowrap">
                                            <span class="font-medium">{{ $rule->days_to_expiry_min }}</span>
                                            <span class="text-slate-400"> - </span>
                                            <span class="font-medium">{{ $rule->days_to_expiry_max }}</span>
                                            <span class="text-slate-500 text-sm">{{ __('days') }}</span>
                                        </td>
                                        <td class="whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $rule->status_label === 'Critical' ? 'bg-red-100 text-red-800' :
                                                   ($rule->status_label === 'Urgent' ? 'bg-orange-100 text-orange-800' :
                                                   ($rule->status_label === 'Approaching' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                                {{ $rule->status_label }}
                                            </span>
                                        </td>
                                        <td class="whitespace-nowrap text-right">
                                            <span class="text-lg font-bold text-green-600">{{ $rule->discount_percentage }}%</span>
                                        </td>
                                        <td class="whitespace-nowrap">
                                            @if ($rule->branch_id)
                                                <span class="text-xs text-slate-500">{{ __('Branch Specific') }}</span>
                                            @else
                                                <span class="text-xs text-blue-600">{{ __('Global') }}</span>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap text-center">
                                            <button wire:click="toggleActive({{ $rule->id }})" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $rule->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $rule->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                            </button>
                                        </td>
                                        <td class="whitespace-nowrap text-center">
                                            <div class="flex items-center justify-center gap-2">
                                                <button wire:click="viewRule({{ $rule->id }})" class="ui-btn-link text-xs">{{ __('View') }}</button>
                                                @if ($this->isSuperAdmin || $rule->branch_id === auth()->user()->branch_id)
                                                    <button wire:click="openModal({{ $rule->id }})" class="ui-btn-link text-xs">{{ __('Edit') }}</button>
                                                    <button wire:click="confirmDelete({{ $rule->id }})" class="ui-btn-link-danger text-xs">{{ __('Delete') }}</button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <p class="text-lg font-medium text-slate-600">{{ __('No Discount Rules') }}</p>
                        <p class="text-sm text-slate-500 mt-1">{{ __('Add rules to automatically calculate clearance discounts.') }}</p>
                        <button wire:click="openModal()" class="ui-btn-primary mt-4">{{ __('Add First Rule') }}</button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal --}}
    @if ($show_modal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="$toggle('show_modal')">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <div class="p-6 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">
                        {{ $editing_id ? __('Edit Discount Rule') : __('Add Discount Rule') }}
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Min Days') }}</label>
                            <input type="number" wire:model="days_to_expiry_min" min="0" max="365" class="ui-input">
                            @error('days_to_expiry_min')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Max Days') }}</label>
                            <input type="number" wire:model="days_to_expiry_max" min="0" max="365" class="ui-input">
                            @error('days_to_expiry_max')
                                <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Status Label') }}</label>
                        <input type="text" wire:model="status_label" class="ui-input" placeholder="{{ __('e.g., Critical, Urgent, Approaching') }}">
                        @error('status_label')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">{{ __('Discount Percentage') }}</label>
                        <div class="flex items-center gap-2">
                            <input type="range" wire:model.live="discount_percentage" min="0" max="100" step="5" class="flex-1">
                            <span class="w-16 text-center font-bold text-green-600 text-lg">{{ $discount_percentage }}%</span>
                        </div>
                        @error('discount_percentage')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="is_active" id="is_active" class="rounded border-slate-300">
                        <label for="is_active" class="text-sm text-slate-700">{{ __('Active') }}</label>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-200 flex justify-end gap-3">
                    <button wire:click="$toggle('show_modal')" class="ui-btn-secondary">{{ __('Cancel') }}</button>
                    <button wire:click="save" class="ui-btn-primary">{{ __('Save Rule') }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- View Modal --}}
    @if ($show_view_modal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeViewModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <div class="p-6 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Discount Rule Details') }}</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">{{ __('Min Days') }}</label>
                            <div class="text-lg font-semibold text-slate-900">{{ $days_to_expiry_min }}</div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-500 mb-1">{{ __('Max Days') }}</label>
                            <div class="text-lg font-semibold text-slate-900">{{ $days_to_expiry_max }}</div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ __('Status Label') }}</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $status_label === 'Critical' ? 'bg-red-100 text-red-800' :
                               ($status_label === 'Urgent' ? 'bg-orange-100 text-orange-800' :
                               ($status_label === 'Approaching' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                            {{ $status_label }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ __('Discount Percentage') }}</label>
                        <div class="text-2xl font-bold text-green-600">{{ $discount_percentage }}%</div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-500 mb-1">{{ __('Status') }}</label>
                        <span class="{{ $is_active ? 'text-green-600' : 'text-red-600' }} font-medium">
                            {{ $is_active ? __('Active') : __('Inactive') }}
                        </span>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-200 flex justify-end">
                    <button wire:click="closeViewModal" class="ui-btn-secondary">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($show_delete_modal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="closeDeleteModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <div class="p-6 border-b border-slate-200 bg-red-50">
                    <h3 class="text-lg font-semibold text-red-900">{{ __('Confirm Delete') }}</h3>
                </div>
                <div class="p-6">
                    <p class="text-slate-700">{{ __('Are you sure you want to delete this discount rule?') }}</p>
                    <p class="mt-2 text-sm font-medium text-slate-900">{{ $deleting_rule_info }}</p>
                    <p class="mt-2 text-xs text-slate-500">{{ __('This action cannot be undone.') }}</p>
                </div>
                <div class="p-6 border-t border-slate-200 flex justify-end gap-3">
                    <button wire:click="closeDeleteModal" class="ui-btn-secondary">{{ __('Cancel') }}</button>
                    <button wire:click="performDelete" class="ui-btn-danger">{{ __('Delete') }}</button>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif
</div>
