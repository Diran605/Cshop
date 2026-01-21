<x-app-layout>
     <x-slot name="header">
         <h2 class="font-semibold text-xl text-gray-800 leading-tight">
             {{ __('Retail Dashboard') }}
         </h2>
     </x-slot>

     <div class="py-12">
         <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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
