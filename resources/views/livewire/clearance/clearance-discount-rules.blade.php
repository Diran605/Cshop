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
                                    <th>{{ __('Days to Expiry') }}</th>
                                    <th>{{ __('Status Label') }}</th>
                                    <th class="text-right">{{ __('Discount %') }}</th>
                                    <th>{{ __('Scope') }}</th>
                                    <th>{{ __('Active') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->discountRules as $rule)
                                    <tr class="{{ ! $rule->is_active ? 'bg-gray-50' : '' }}">
                                        <td>
                                            <span class="font-medium">{{ $rule->days_to_expiry_min }}</span>
                                            <span class="text-slate-400"> - </span>
                                            <span class="font-medium">{{ $rule->days_to_expiry_max }}</span>
                                            <span class="text-slate-500 text-sm">{{ __('days') }}</span>
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                {{ $rule->status_label === 'Critical' ? 'bg-red-100 text-red-800' :
                                                   ($rule->status_label === 'Urgent' ? 'bg-orange-100 text-orange-800' :
                                                   ($rule->status_label === 'Approaching' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800')) }}">
                                                {{ $rule->status_label }}
                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <span class="text-lg font-bold text-green-600">{{ $rule->discount_percentage }}%</span>
                                        </td>
                                        <td>
                                            @if ($rule->branch_id)
                                                <span class="text-xs text-slate-500">{{ __('This Branch') }}</span>
                                            @else
                                                <span class="text-xs text-blue-600">{{ __('Global (All Branches)') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button wire:click="toggleActive({{ $rule->id }})" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $rule->is_active ? 'bg-green-500' : 'bg-gray-300' }}">
                                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $rule->is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                            </button>
                                        </td>
                                        <td>
                                            @if ($rule->branch_id === auth()->user()->branch_id)
                                                <div class="flex gap-2">
                                                    <button wire:click="openModal({{ $rule->id }})" class="ui-btn-link">
                                                        {{ __('Edit') }}
                                                    </button>
                                                    <button wire:click="delete({{ $rule->id }})" class="ui-btn-link-danger" onclick="return confirm('{{ __('Are you sure?') }}')">
                                                        {{ __('Delete') }}
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-xs text-slate-400">{{ __('Read only') }}</span>
                                            @endif
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

    @if (session()->has('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            {{ session('success') }}
        </div>
    @endif
</div>
