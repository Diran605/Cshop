<div class="py-8">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="mb-6">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Stock Movement Audit Trail') }}
            </h2>
        </div>

        <div class="bg-white shadow-sm sm:rounded-lg">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('From') }}</label>
                        <input type="date" wire:model.live="date_from" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('To') }}</label>
                        <input type="date" wire:model.live="date_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Branch') }}</label>
                        <select wire:model.live="branch_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" @if (! $isSuperAdmin) disabled @endif>
                            @if ($isSuperAdmin)
                                <option value="0">{{ __('All') }}</option>
                            @endif
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Product') }}</label>
                        <select wire:model.live="product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="0">{{ __('All') }}</option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Type') }}</label>
                        <select wire:model.live="movement_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="all">{{ __('All') }}</option>
                            <option value="IN">{{ __('IN') }}</option>
                            <option value="OUT">{{ __('OUT') }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">{{ __('Search') }}</label>
                        <input type="text" wire:model.debounce.300ms="search" placeholder="{{ __('Product/User') }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                    </div>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Date') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Branch') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Product') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Type') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Qty') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Before') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('After') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Unit Cost') }}</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Unit Price') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('User') }}</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Ref') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($movements as $m)
                                <tr wire:key="mv-{{ $m->id }}">
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ optional($m->moved_at)->format('Y-m-d H:i') }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $m->branch?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900">{{ $m->product?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm font-medium {{ $m->movement_type === 'IN' ? 'text-green-700' : 'text-red-700' }}">{{ $m->movement_type }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ (int) $m->quantity }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 text-right">{{ (int) $m->before_stock }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 text-right">{{ (int) $m->after_stock }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 text-right">{{ $m->unit_cost !== null ? number_format((float) $m->unit_cost, 2) : '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700 text-right">{{ $m->unit_price !== null ? number_format((float) $m->unit_price, 2) : '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ $m->user?->name ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">
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
                                    <td colspan="11" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No movements found.') }}</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
