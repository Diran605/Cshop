<x-app-layout>
     <x-slot name="header">
        <div>
            <h2 class="ui-page-title">
                {{ __('Retail Dashboard') }}
            </h2>
            <div class="ui-page-subtitle">
                @if (auth()->user() && auth()->user()->branch)
                    {{ __('Branch:') }}
                    <span class="font-medium">{{ auth()->user()->branch->name }}</span>
                @elseif (auth()->user() && auth()->user()->role === 'super_admin')
                    <span class="font-medium">{{ __('Super Admin') }}</span>
                @endif
            </div>
        </div>
    </x-slot>

     <div class="ui-page">
        <div class="ui-page-container">
            {{-- KPI Cards --}}
            <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="ui-kpi-card">
                    <div>
                        <div class="ui-kpi-title">
                            {{ __('Total Sales (This Month)') }}
                        </div>
                        <div class="ui-kpi-value">
                            XAF {{ number_format((float) $salesTotal, 0, ',', ' ') }}
                        </div>
                    </div>
                </div>

                <div class="ui-kpi-card">
                    <div>
                        <div class="ui-kpi-title">
                            {{ __('Inventory Value') }}
                        </div>
                        <div class="ui-kpi-value">
                            XAF {{ number_format((float) $inventoryValue, 0, ',', ' ') }}
                        </div>
                    </div>
                </div>

                <div class="ui-kpi-card bg-red-50 border-red-200">
                    <div>
                        <div class="ui-kpi-title text-red-600">
                            {{ __('Low Stock Items') }}
                        </div>
                        <div class="ui-kpi-value text-red-700">
                            {{ $lowStockCount ?? 0 }}
                        </div>
                    </div>
                </div>

                <div class="ui-kpi-card bg-amber-50 border-amber-200">
                    <div>
                        <div class="ui-kpi-title text-amber-600">
                            {{ __('Expiring Soon') }}
                        </div>
                        <div class="ui-kpi-value text-amber-700">
                            {{ $expiringCount ?? 0 }}
                        </div>
                    </div>
                </div>
            </div>

            @if (! $isSuperAdmin)
                @canany(['alerts.stock_adjustment', 'alerts.expired_stock', 'alerts.expiry_warning', 'alerts.low_stock'])
                    <div class="mb-6" wire:ignore>
                        <livewire:dashboard-alerts />
                    </div>
                @endcanany
            @endif

            @if (! $isSuperAdmin)
                <div class="mb-6 ui-card">
                    <div class="ui-card-body">
                        <h3 class="ui-card-title">{{ __('Inventory Value by Category') }}</h3>
                        <div class="mt-4 overflow-x-auto">
                            <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Category') }}</th>
                                        <th class="text-right">{{ __('Value') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($inventoryByCategory as $row)
                                        <tr>
                                            <td>{{ $row->category_name }}</td>
                                            <td class="text-right">XAF {{ number_format((float) $row->inventory_value, 0, ',', ' ') }}</td>
                                        </tr>
                                    @endforeach
                                    @if (count($inventoryByCategory) === 0)
                                        <tr>
                                            <td colspan="2" class="text-center text-sm text-slate-500">{{ __('No inventory data found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="mb-6 ui-card">
                    <div class="ui-card-body">
                        <h3 class="ui-card-title">{{ __('Top Branches by Sales (This Month)') }}</h3>
                        <div class="mt-4 overflow-x-auto">
                            <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('Branch') }}</th>
                                        <th class="text-right">{{ __('Sales') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($topBranchesBySales as $row)
                                        <tr>
                                            <td>{{ $row->branch_name }}</td>
                                            <td class="text-right">XAF {{ number_format((float) $row->sales_total, 0, ',', ' ') }}</td>
                                        </tr>
                                    @endforeach
                                    @if (count($topBranchesBySales) === 0)
                                        <tr>
                                            <td colspan="2" class="text-center text-sm text-slate-500">{{ __('No sales data found.') }}</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="ui-card">
                    <a href="{{ route('products.index') }}" class="block p-6 hover:bg-slate-50/70">
                        <div class="text-sm text-slate-600">Setup</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">Products</div>
                        <div class="mt-2 text-sm text-slate-600">Manage product catalog, pricing, and bulk settings.</div>
                    </a>
                </div>
 
                 <div class="ui-card">
                     <a href="{{ route('setup.categories') }}" class="block p-6 hover:bg-slate-50/70">
                         <div class="text-sm text-slate-600">Setup</div>
                         <div class="mt-1 text-lg font-semibold text-slate-900">Categories</div>
                         <div class="mt-2 text-sm text-slate-600">Create and organize product categories.</div>
                     </a>
                 </div>
 
                 <div class="ui-card">
                     <a href="{{ route('setup.bulk_types') }}" class="block p-6 hover:bg-slate-50/70">
                         <div class="text-sm text-slate-600">Setup</div>
                         <div class="mt-1 text-lg font-semibold text-slate-900">Bulk Units & Types</div>
                         <div class="mt-2 text-sm text-slate-600">Define packaging units and reusable bulk configurations.</div>
                         <div class="mt-3 text-sm font-semibold text-primary-blue">
                             {{ __('Go to Bulk Units') }}
                         </div>
                     </a>
                 </div>
 
                 <div class="ui-card">
                     <a href="{{ route('stock_in.index') }}" class="block p-6 hover:bg-slate-50/70">
                         <div class="text-sm text-slate-600">Operations</div>
                         <div class="mt-1 text-lg font-semibold text-slate-900">Stock In</div>
                         <div class="mt-2 text-sm text-slate-600">Receive inventory and generate receipts.</div>
                     </a>
                 </div>
 
                 <div class="ui-card">
                     <a href="{{ route('sales.add') }}" class="block p-6 hover:bg-slate-50/70">
                         <div class="text-sm text-slate-600">Operations</div>
                         <div class="mt-1 text-lg font-semibold text-slate-900">Sales</div>
                         <div class="mt-2 text-sm text-slate-600">Process transactions with stock validation.</div>
                     </a>
                 </div>
 
                 <div class="ui-card">
                     <a href="{{ route('reports.index') }}" class="block p-6 hover:bg-slate-50/70">
                         <div class="text-sm text-slate-600">Analytics</div>
                         <div class="mt-1 text-lg font-semibold text-slate-900">Reports</div>
                         <div class="mt-2 text-sm text-slate-600">View sales, inventory, and movement reports.</div>
                     </a>
                 </div>
             </div>
         </div>
     </div>
 </x-app-layout>
