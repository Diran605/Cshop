<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Products') }}</h2>
            <div class="ui-page-subtitle">{{ __('Manage your catalog and product settings.') }}</div>
        </div>

        <div class="space-y-6">
            @if ($mode === 'add')
                <div class="ui-card">
                    <div class="ui-card-body">
                        <div class="flex items-center justify-between">
                            <h3 class="ui-card-title">
                                {{ $editingId ? __('Edit Product') : __('Add Product') }}
                            </h3>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="ui-label">{{ __('Branch') }}</label>
                            @if ($isSuperAdmin)
                                <select wire:model.live="branch_id" @disabled($editingId) class="mt-1 ui-select">
                                    <option value="0">{{ __('Select...') }}</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                                @error('branch_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            @else
                                <div class="mt-1 rounded-lg border border-slate-300/80 bg-white/60 px-3 py-2 text-sm text-slate-700">
                                    {{ auth()->user()?->branch?->name ?? '-' }}
                                </div>
                                <input type="hidden" wire:model="branch_id" />
                            @endif
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Name') }}</label>
                            <input type="text" wire:model.defer="name" class="mt-1 ui-input" />
                            @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Category') }}</label>
                            <select wire:model.defer="category_id" class="mt-1 ui-select">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Selling Price') }}</label>
                            <input type="number" min="0" step="0.01" wire:model.defer="selling_price" class="mt-1 ui-input" />
                            @error('selling_price') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Cost Price (optional)') }}</label>
                            <input type="number" min="0" step="0.01" wire:model.defer="cost_price" class="mt-1 ui-input" />
                            @error('cost_price') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Minimum Selling Price (optional)') }}</label>
                            <input type="number" min="0" step="0.01" wire:model.defer="min_selling_price" class="mt-1 ui-input" />
                            @error('min_selling_price') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Status') }}</label>
                            <select wire:model.defer="status" class="mt-1 ui-select">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                            @error('status') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model.live="bulk_enabled" class="ui-checkbox" />
                                <span class="ms-2 text-sm text-slate-700">{{ __('Enable Bulk') }}</span>
                            </label>
                            @error('bulk_enabled') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Bulk Type') }}</label>
                            <select wire:model.defer="bulk_type_id" @disabled(! $bulk_enabled) class="mt-1 ui-select">
                                <option value="">{{ __('Select...') }}</option>
                                @foreach ($bulkTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('bulk_type_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Description') }}</label>
                            <textarea wire:model.defer="description" rows="3" class="mt-1 ui-input"></textarea>
                            @error('description') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        @if (! $editingId)
                            <div class="md:col-span-2">
                                <div class="ui-muted-panel">
                                    <div class="text-sm font-semibold text-slate-700">{{ __('Opening Stock (optional)') }}</div>
                                    <div class="mt-3 grid grid-cols-1 md:grid-cols-3 gap-4">
                                        <div>
                                            <label class="ui-label">{{ __('Opening Quantity') }}</label>
                                            <input type="number" min="0" wire:model.defer="opening_quantity" class="mt-1 ui-input" />
                                            @error('opening_quantity') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                        </div>
                                        <div>
                                            <label class="ui-label">{{ __('Opening Cost Price (optional)') }}</label>
                                            <input type="number" min="0" step="0.01" wire:model.defer="opening_cost_price" class="mt-1 ui-input" />
                                            @error('opening_cost_price') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                        </div>
                                        <div>
                                            <label class="ui-label">{{ __('Opening Expiry Date (optional)') }}</label>
                                            <input type="date" wire:model.defer="opening_expiry_date" class="mt-1 ui-input" />
                                            @error('opening_expiry_date') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="mt-2 text-xs text-slate-500">
                                        {{ __('If you enter an opening quantity, the system will post an Opening Stock receipt so expiry tracking and FEFO sales work correctly.') }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="md:col-span-2 flex items-center justify-end gap-3">
                            @if ($editingId)
                                <button type="button" wire:click="cancelEdit" class="ui-btn-secondary">
                                    {{ __('Cancel') }}
                                </button>
                            @endif

                            <button type="button" wire:click="save" class="ui-btn-primary">
                                {{ $editingId ? __('Save') : __('Create') }}
                            </button>
                        </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($mode === 'manage' || $mode === 'expired')
                <div class="ui-card">
                    <div class="ui-card-body">
                        <div class="flex items-center justify-between gap-4">
                            <div class="w-full max-w-md">
                                <label class="ui-label">{{ __('Search') }}</label>
                                <input type="text" wire:model.live.debounce.300ms="search" class="mt-1 ui-input" placeholder="Search products..." />
                            </div>
                        </div>

                        <div class="overflow-x-auto mt-4">
                            <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        @if ($isSuperAdmin)
                                            <th>{{ __('Branch') }}</th>
                                        @endif
                                        <th>{{ __('Name') }}</th>
                                        @if ($mode === 'expired')
                                            <th>{{ __('Expired Qty') }}</th>
                                        @endif
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Cost') }}</th>
                                        <th>{{ __('Min Price') }}</th>
                                        <th>{{ __('Price') }}</th>
                                        <th>{{ __('Bulk Type') }}</th>
                                        <th>{{ __('Status') }}</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                        <tr wire:key="product-{{ $product->id }}">
                                            @if ($isSuperAdmin)
                                                <td>
                                                    {{ $product->branch?->name ?? '-' }}
                                                </td>
                                            @endif
                                            <td>
                                                <div class="font-medium">{{ $product->name }}</div>
                                                @if ($product->description)
                                                    <div class="text-xs text-slate-500">{{ $product->description }}</div>
                                                @endif
                                            </td>

                                            @if ($mode === 'expired')
                                                <td>
                                                    {{ (int) ($expiredQtyMap[$product->id] ?? 0) }}
                                                </td>
                                            @endif
                                            <td>
                                                {{ $product->category?->name ?? '-' }}
                                            </td>
                                            <td>
                                                {{ $product->cost_price !== null ? number_format((float) $product->cost_price, 2) : '-' }}
                                            </td>
                                            <td>
                                                {{ $product->min_selling_price !== null ? number_format((float) $product->min_selling_price, 2) : '-' }}
                                            </td>
                                            <td>
                                                {{ number_format((float) $product->selling_price, 2) }}
                                            </td>
                                            <td>
                                                @if ($product->bulk_enabled)
                                                    {{ $product->bulkType?->name ?? '-' }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                @if ((string) $product->status === 'active')
                                                    <span class="ui-badge-success">{{ __('Active') }}</span>
                                                @else
                                                    <span class="ui-badge-warning">{{ __('Inactive') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="inline-flex items-center gap-3">
                                                    <button type="button" wire:click.stop.prevent="openEditModal({{ $product->id }})" class="ui-btn-link">{{ __('Edit') }}</button>
                                                    <button type="button" wire:click.stop.prevent="openDeleteModal({{ $product->id }})" class="ui-btn-link-danger">{{ __('Delete') }}</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if ($products->isEmpty())
                                        <tr>
                                            <td colspan="{{ ($isSuperAdmin ? 9 : 8) + ($mode === 'expired' ? 1 : 0) }}" class="ui-table-empty">{{ __('No products found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($show_edit_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeEditModal" data-modal-overlay></div>
            <div class="relative w-full max-w-3xl mx-4 ui-card">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Edit Product') }}</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $name ?: '-' }}</div>
                    </div>
                    <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                </div>

                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="ui-label">{{ __('Branch') }}</label>
                            @if ($isSuperAdmin)
                                <select wire:model.live="branch_id" @disabled(true) class="mt-1 ui-select" disabled>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <div class="mt-1 rounded-lg border border-slate-300/80 bg-white/60 px-3 py-2 text-sm text-slate-700">
                                    {{ auth()->user()?->branch?->name ?? '-' }}
                                </div>
                            @endif
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Name') }}</label>
                            <input type="text" wire:model.defer="name" class="mt-1 ui-input" />
                            @error('name') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Category') }}</label>
                            <select wire:model.defer="category_id" class="mt-1 ui-select">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Selling Price') }}</label>
                            <input type="number" min="0" step="0.01" wire:model.defer="selling_price" class="mt-1 ui-input" />
                            @error('selling_price') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Cost Price (optional)') }}</label>
                            <input type="number" min="0" step="0.01" wire:model.defer="cost_price" class="mt-1 ui-input" />
                            @error('cost_price') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Minimum Selling Price (optional)') }}</label>
                            <input type="number" min="0" step="0.01" wire:model.defer="min_selling_price" class="mt-1 ui-input" />
                            @error('min_selling_price') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Status') }}</label>
                            <select wire:model.defer="status" class="mt-1 ui-select">
                                <option value="active">{{ __('Active') }}</option>
                                <option value="inactive">{{ __('Inactive') }}</option>
                            </select>
                            @error('status') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" wire:model.live="bulk_enabled" class="ui-checkbox" />
                                <span class="ms-2 text-sm text-slate-700">{{ __('Enable Bulk') }}</span>
                            </label>
                            @error('bulk_enabled') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div>
                            <label class="ui-label">{{ __('Bulk Type') }}</label>
                            <select wire:model.defer="bulk_type_id" @disabled(! $bulk_enabled) class="mt-1 ui-select">
                                <option value="">{{ __('Select...') }}</option>
                                @foreach ($bulkTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                            @error('bulk_type_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

                        <div class="md:col-span-2">
                            <label class="ui-label">{{ __('Description') }}</label>
                            <textarea wire:model.defer="description" rows="3" class="mt-1 ui-input"></textarea>
                            @error('description') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-3">
                        <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>
                            {{ __('Cancel') }}
                        </button>
                        <button type="button" wire:click="save" class="ui-btn-primary">
                            {{ __('Save') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if ($show_delete_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeDeleteModal" data-modal-overlay></div>
            <div class="relative w-full max-w-lg mx-4 ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="text-sm text-slate-500">{{ __('Confirm Delete') }}</div>
                    <div class="mt-1 font-semibold text-slate-900">{{ __('Delete Product') }}</div>
                </div>

                <div class="p-4">
                    <div class="text-sm text-slate-700">
                        {{ __('Are you sure you want to delete this product?') }}
                        <span class="font-semibold">{{ $pending_delete_name ?: '-' }}</span>
                    </div>

                    <div class="mt-4 flex items-center justify-end gap-3">
                        <button type="button" wire:click="closeDeleteModal" class="ui-btn-secondary" data-modal-close>{{ __('Cancel') }}</button>
                        <button type="button" wire:click="confirmDelete" class="ui-btn-danger">{{ __('Delete') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Smile, breathe, and go slowly. - Thich Nhat Hanh --}}
</div>
