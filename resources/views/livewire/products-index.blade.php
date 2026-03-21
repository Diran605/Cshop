<div class="ui-page">
    <div class="ui-page-container">
        @if (session()->has('status'))
            <div class="mb-4 ui-alert ui-alert-success">
                {{ session('status') }}
            </div>
        @endif

        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Products') }}</h2>
            <div class="ui-page-subtitle">{{ __('Manage your catalog and product settings.') }}</div>
            <div class="mt-4 flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <a href="{{ route('products.download-template') }}" class="ui-btn-secondary w-full sm:w-auto text-center">
                    {{ __('Download Template') }}
                </a>
                <label class="ui-btn-secondary cursor-pointer w-full sm:w-auto text-center">
                    {{ __('Import Excel') }}
                    <input type="file" wire:model="excel_file" accept=".xlsx,.xls" class="hidden" />
                </label>
                @if ($excel_file)
                    <button type="button" wire:click="importExcel" class="ui-btn-primary w-full sm:w-auto">
                        {{ __('Upload') }}
                    </button>
                @endif
            </div>
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
                            <label class="ui-label">{{ __('Product Date') }}</label>
                            <input type="date" wire:model.defer="product_date" class="mt-1 ui-input" />
                            @error('product_date') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
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
                            <label class="ui-label">{{ __('Unit Type') }}</label>
                            <select wire:model.defer="unit_type_id" class="mt-1 ui-select">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($unitTypes as $unitType)
                                    <option value="{{ $unitType->id }}">{{ $unitType->name }}</option>
                                @endforeach
                            </select>
                            @error('unit_type_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
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

                        <div class="md:col-span-2 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                            @if ($editingId)
                                <button type="button" wire:click="cancelEdit" class="ui-btn-secondary w-full sm:w-auto">
                                    {{ __('Cancel') }}
                                </button>
                            @endif

                            <button type="button" wire:click="save" class="ui-btn-primary w-full sm:w-auto">
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
                        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                                <div class="w-full sm:max-w-md">
                                    <label class="ui-label">{{ __('Search') }}</label>
                                    <input type="text" wire:model.live.debounce.300ms="search" class="mt-1 ui-input" placeholder="Search products..." />
                                </div>
                                <div class="w-full sm:w-48">
                                    <label class="ui-label">{{ __('Status') }}</label>
                                    <select wire:model.live="status_filter" class="mt-1 ui-select">
                                        <option value="active">{{ __('Active') }}</option>
                                        <option value="void_pending">{{ __('Void Pending') }}</option>
                                        <option value="voided">{{ __('Voided') }}</option>
                                        <option value="all">{{ __('All') }}</option>
                                    </select>
                                </div>
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
                                        <th>{{ __('Stock') }}</th>
                                        @if ($mode === 'expired')
                                            <th>{{ __('Expired Qty') }}</th>
                                        @endif
                                        <th>{{ __('Category') }}</th>
                                        <th>{{ __('Cost') }}</th>
                                        <th>{{ __('WAC') }}</th>
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
                                            <td>
                                                {{ $product->current_stock ?? 0 }}
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
                                                {{ $product->weighted_average_cost !== null ? number_format((float) $product->weighted_average_cost, 2) : '-' }}
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
                                                @elseif ((string) $product->status === 'void_pending')
                                                    <span class="ui-badge-warning">{{ __('Void Pending') }}</span>
                                                @elseif ((string) $product->status === 'voided')
                                                    <span class="ui-badge-danger">{{ __('Voided') }}</span>
                                                @else
                                                    <span class="ui-badge-info">{{ __('Inactive') }}</span>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    @can('products.view')
                                                        <button type="button" wire:click="viewProduct({{ $product->id }})" class="ui-btn-link text-xs">{{ __('View') }}</button>
                                                    @endcan
                                                    @can('products.edit')
                                                        <button type="button" wire:click="openEditModal({{ $product->id }})" class="ui-btn-link text-xs">{{ __('Edit') }}</button>
                                                    @endcan
                                                    @can('products.void')
                                                        @if ((string) $product->status === 'active')
                                                            <button type="button" wire:click="openVoidModal({{ $product->id }})" class="ui-btn-link-danger text-xs">{{ __('Void') }}</button>
                                                        @endif
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                    @if ($products->isEmpty())
                                        <tr>
                                            <td colspan="{{ ($isSuperAdmin ? 10 : 9) + ($mode === 'expired' ? 1 : 0) }}" class="ui-table-empty">{{ __('No products found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            </div>

                            @if (method_exists($products, 'hasPages') && $products->hasPages())
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="text-sm text-slate-600">
                                        {{ __('Showing') }} {{ $products->firstItem() }} {{ __('to') }} {{ $products->lastItem() }} {{ __('of') }} {{ $products->total() }} {{ __('results') }}
                                    </div>
                                    {{ $products->links('pagination::tailwind') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- View Product Modal --}}
    @if ($show_view_modal && $viewing_product)
        <div wire:key="view-modal-{{ $viewing_product->id }}" class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeViewModal" data-modal-overlay></div>
            <div class="relative w-full max-w-2xl ui-card max-h-[90vh] overflow-y-auto">
                <div class="p-4 border-b border-slate-200 sticky top-0 bg-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Product Details') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ $viewing_product->name }}</div>
                        </div>
                        <button type="button" wire:click="closeViewModal" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="p-4 space-y-4">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Branch') }}</div>
                            <div class="font-medium">{{ $viewing_product->branch?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Category') }}</div>
                            <div class="font-medium">{{ $viewing_product->category?->name ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Status') }}</div>
                            <div class="font-medium">
                                @if ((string) $viewing_product->status === 'active')
                                    <span class="ui-badge-success">{{ __('Active') }}</span>
                                @elseif ((string) $viewing_product->status === 'void_pending')
                                    <span class="ui-badge-warning">{{ __('Void Pending') }}</span>
                                @elseif ((string) $viewing_product->status === 'voided')
                                    <span class="ui-badge-danger">{{ __('Voided') }}</span>
                                @else
                                    <span class="ui-badge-info">{{ __('Inactive') }}</span>
                                @endif
                            </div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Unit Type') }}</div>
                            <div class="font-medium">{{ $viewing_product->unitType?->name ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-lg p-4">
                        <div class="text-xs text-slate-500 mb-3">{{ __('Pricing') }}</div>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <div class="text-lg font-mono">{{ $viewing_product->cost_price !== null ? number_format((float) $viewing_product->cost_price, 2) : '-' }}</div>
                                <div class="text-xs text-slate-500">{{ __('Cost Price') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-mono">{{ $viewing_product->min_selling_price !== null ? number_format((float) $viewing_product->min_selling_price, 2) : '-' }}</div>
                                <div class="text-xs text-slate-500">{{ __('Min Price') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-mono font-bold text-green-600">{{ number_format((float) $viewing_product->selling_price, 2) }}</div>
                                <div class="text-xs text-slate-500">{{ __('Selling Price') }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-slate-50 rounded-lg p-4">
                        <div class="text-xs text-slate-500 mb-3">{{ __('Stock') }}</div>
                        <div class="grid grid-cols-3 gap-4">
                            <div class="text-center">
                                <div class="text-2xl font-mono font-bold">{{ $viewing_product->stock?->current_stock ?? 0 }}</div>
                                <div class="text-xs text-slate-500">{{ __('Current Stock') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-mono">{{ $viewing_product->stock?->cost_price !== null ? number_format((float) $viewing_product->stock->cost_price, 2) : '-' }}</div>
                                <div class="text-xs text-slate-500">{{ __('Stock Cost') }}</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-mono font-bold text-blue-600">{{ $viewing_product->stock ? number_format((float) $viewing_product->stock->current_stock * (float) $viewing_product->stock->cost_price, 2) : '0.00' }}</div>
                                <div class="text-xs text-slate-500">{{ __('Stock Value') }}</div>
                            </div>
                        </div>
                    </div>

                    @if ($viewing_product->bulk_enabled)
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <div class="text-xs text-blue-600 font-medium mb-2">{{ __('Bulk Settings') }}</div>
                            <div class="text-sm text-blue-800">
                                <strong>{{ __('Bulk Type:') }}</strong> {{ $viewing_product->bulkType?->name ?? '-' }}
                            </div>
                        </div>
                    @endif

                    @if ($viewing_product->description)
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Description') }}</div>
                            <div class="mt-1 text-sm bg-slate-50 rounded p-3">{{ $viewing_product->description }}</div>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Created') }}</div>
                            <div class="font-medium">{{ $viewing_product->created_at->format('M j, Y H:i') }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-slate-500">{{ __('Last Updated') }}</div>
                            <div class="font-medium">{{ $viewing_product->updated_at->format('M j, Y H:i') }}</div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex justify-end gap-3">
                    <button type="button" wire:click="closeViewModal" class="ui-btn-primary">{{ __('Close') }}</button>
                </div>
            </div>
        </div>
    @endif

    @if ($show_edit_modal)
        <div class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-8 sm:pt-12 overflow-y-auto" data-modal-root>
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeEditModal" data-modal-overlay></div>
            <div class="relative w-full max-w-3xl ui-card flex flex-col mb-4 z-10">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between shrink-0">
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Edit Product') }}</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $name ?: '-' }}</div>
                    </div>
                    <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                </div>

                <div class="p-4 overflow-y-auto flex-1 min-h-0">
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
                            <label class="ui-label">{{ __('Unit Type') }}</label>
                            <select wire:model.defer="unit_type_id" class="mt-1 ui-select">
                                <option value="">{{ __('None') }}</option>
                                @foreach ($unitTypes as $unitType)
                                    <option value="{{ $unitType->id }}">{{ $unitType->name }}</option>
                                @endforeach
                            </select>
                            @error('unit_type_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
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

                        <div>
                            <label class="ui-label">{{ __('Product Date') }}</label>
                            <input type="date" wire:model.defer="product_date" class="mt-1 ui-input" />
                            @error('product_date') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>

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
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 shrink-0">
                    <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>
                        {{ __('Cancel') }}
                    </button>
                    <button type="button" wire:click="save" class="ui-btn-primary">
                        {{ __('Save') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Void Modal --}}
    @if ($show_void_modal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeVoidModal" data-modal-overlay></div>
            <div class="relative w-full max-w-md ui-card">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <div class="text-sm text-slate-500">{{ __('Request Product Void') }}</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $pending_void_name }}</div>
                    </div>
                    <button type="button" wire:click="closeVoidModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                </div>

                <div class="p-4">
                    <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-orange-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <div class="text-sm text-orange-800">
                                {{ __('This will create a pending stock adjustment to zero out the product\'s stock. The product will be locked from sales until the adjustment is approved or rejected.') }}
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-4">
                        <div class="text-xs text-blue-700">
                            <strong>{{ __('Approval Flow:') }}</strong>
                            {{ __('After submission, an authorized user must approve or reject the void request in Stock Adjustments.') }}
                        </div>
                    </div>

                    <div>
                        <label class="ui-label">{{ __('Reason (required, min 10 characters)') }}</label>
                        <textarea wire:model.live="void_reason" rows="3" class="mt-1 ui-input" placeholder="e.g., Duplicate of product ID #456 - entered by error"></textarea>
                        @error('void_reason') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                    <button type="button" wire:click="closeVoidModal" class="ui-btn-secondary" data-modal-close>{{ __('Cancel') }}</button>
                    <button type="button" wire:click="confirmVoid" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-medium">
                        {{ __('Submit Void Request') }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Smile, breathe, and go slowly. - Thich Nhat Hanh --}}
</div>
