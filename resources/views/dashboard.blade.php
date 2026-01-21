<x-app-layout>
     <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Retail Dashboard') }}
            </h2>
            <div class="mt-1 text-sm text-gray-600">
                @if (auth()->user() && auth()->user()->role === 'branch_admin')
                    {{ __('Branch:') }}
                    <span class="font-medium">{{ auth()->user()->branch?->name ?? '-' }}</span>
                @elseif (auth()->user() && auth()->user()->role === 'super_admin')
                    <span class="font-medium">{{ __('Super Admin') }}</span>
                @endif
            </div>
        </div>
    </x-slot>

     <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">
                            {{ $isSuperAdmin ? __('Total Sales (This Month)') : __('Total Sales (This Month)') }}
                        </div>
                        <div class="mt-2 text-2xl font-semibold text-gray-900">
                            {{ number_format((float) $salesTotal, 2) }}
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">
                            {{ __('Inventory Value') }}
                        </div>
                        <div class="mt-2 text-2xl font-semibold text-gray-900">
                            {{ number_format((float) $inventoryValue, 2) }}
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-sm text-gray-500">
                            {{ __('Low Stock Value') }}
                        </div>
                        <div class="mt-2 text-2xl font-semibold text-gray-900">
                            {{ number_format((float) $lowStockValue, 2) }}
                        </div>
                    </div>
                </div>
            </div>

            @if (! $isSuperAdmin)
                <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Inventory Value by Category') }}</h3>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Category') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Value') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($inventoryByCategory as $row)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $row->category_name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format((float) $row->inventory_value, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    @if (count($inventoryByCategory) === 0)
                                        <tr>
                                            <td colspan="2" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No inventory data found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900">{{ __('Top Branches by Sales (This Month)') }}</h3>
                        <div class="mt-4 overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Branch') }}</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('Sales') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($topBranchesBySales as $row)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $row->branch_name }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 text-right">{{ number_format((float) $row->sales_total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    @if (count($topBranchesBySales) === 0)
                                        <tr>
                                            <td colspan="2" class="px-4 py-8 text-center text-sm text-gray-500">{{ __('No sales data found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <a href="{{ route('products.index') }}" class="block p-6 hover:bg-gray-50">
                        <div class="text-sm text-gray-500">Setup</div>
                        <div class="mt-1 text-lg font-semibold text-gray-900">Products</div>
                        <div class="mt-2 text-sm text-gray-600">Manage product catalog, pricing, and bulk settings.</div>
                    </a>
                </div>
 
                 <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                     <a href="{{ route('setup.categories') }}" class="block p-6 hover:bg-gray-50">
                         <div class="text-sm text-gray-500">Setup</div>
                         <div class="mt-1 text-lg font-semibold text-gray-900">Categories</div>
                         <div class="mt-2 text-sm text-gray-600">Create and organize product categories.</div>
                     </a>
                 </div>
 
                 <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                     <a href="{{ route('setup.bulk_types') }}" class="block p-6 hover:bg-gray-50">
                         <div class="text-sm text-gray-500">Setup</div>
                         <div class="mt-1 text-lg font-semibold text-gray-900">Bulk Units & Types</div>
                         <div class="mt-2 text-sm text-gray-600">Define packaging units and reusable bulk configurations.</div>
                         <div class="mt-3 text-sm text-indigo-600 underline">
                             {{ __('Go to Bulk Units') }}
                         </div>
                     </a>
                 </div>
 
                 <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                     <a href="{{ route('stock_in.index') }}" class="block p-6 hover:bg-gray-50">
                         <div class="text-sm text-gray-500">Operations</div>
                         <div class="mt-1 text-lg font-semibold text-gray-900">Stock In</div>
                         <div class="mt-2 text-sm text-gray-600">Receive inventory and generate receipts.</div>
                     </a>
                 </div>
 
                 <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                     <a href="{{ route('sales.index') }}" class="block p-6 hover:bg-gray-50">
                         <div class="text-sm text-gray-500">Operations</div>
                         <div class="mt-1 text-lg font-semibold text-gray-900">Sales</div>
                         <div class="mt-2 text-sm text-gray-600">Process transactions with stock validation.</div>
                     </a>
                 </div>
 
                 <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                     <a href="{{ route('reports.index') }}" class="block p-6 hover:bg-gray-50">
                         <div class="text-sm text-gray-500">Analytics</div>
                         <div class="mt-1 text-lg font-semibold text-gray-900">Reports</div>
                         <div class="mt-2 text-sm text-gray-600">View sales, inventory, and movement reports.</div>
                     </a>
                 </div>
             </div>
         </div>
     </div>
 </x-app-layout>
