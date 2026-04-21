<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Retail_Sm') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <div class="ui-shell">
            <div class="ui-shell-inner">
            <aside class="ui-sidebar">
                <div class="ui-sidebar-header">
                    <a href="{{ route('dashboard') }}" class="ui-sidebar-brand">
                        {{ config('app.name') }}
                    </a>
                </div>

                <nav class="ui-nav">
                    <a href="{{ route('dashboard') }}"
                        class="ui-nav-link {{ request()->routeIs('dashboard') ? 'ui-nav-link-active' : '' }}">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            {{ __('Dashboard') }}
                        </span>
                    </a>

                    <div class="pt-2">
                        <div class="ui-nav-section-title">{{ __('Setup') }}</div>
                        <div class="mt-2 space-y-1">
                            @can('branches.manage')
                                <a href="{{ route('setup.branches') }}"
                                    class="ui-nav-link {{ request()->routeIs('setup.branches') ? 'ui-nav-link-active' : '' }}">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        {{ __('Branches') }}
                                    </span>
                                </a>
                            @endcan

                            @can('users.manage')
                                <a href="{{ route('users.index') }}"
                                    class="ui-nav-link {{ request()->routeIs('users.*') ? 'ui-nav-link-active' : '' }}">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        {{ __('Users') }}
                                    </span>
                                </a>
                            @endcan

                            @can('setup.categories.manage')
                                <a href="{{ route('setup.categories') }}"
                                    class="ui-nav-link {{ request()->routeIs('setup.categories') ? 'ui-nav-link-active' : '' }}">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        {{ __('Categories') }}
                                    </span>
                                </a>
                            @endcan

                            @can('setup.unit_types.manage')
                                <a href="{{ route('setup.unit_types') }}"
                                    class="ui-nav-link {{ request()->routeIs('setup.unit_types') ? 'ui-nav-link-active' : '' }}">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9m6-9l-3 9m3-9l3 1m-3-1l3-9a5.002 5.002 0 00-6.001 0M18 7l3 9a5.002 5.002 0 01-6.001 0M3 6l3 1m0 0l3 9m6-9l-3 9m6-9l3 1" />
                                        </svg>
                                        {{ __('Unit Types') }}
                                    </span>
                                </a>
                            @endcan

                            @can('setup.bulk.manage')
                                <details class="ui-nav-group" {{ request()->routeIs('setup.bulk_units') || request()->routeIs('setup.bulk_types') ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ request()->routeIs('setup.bulk_units') || request()->routeIs('setup.bulk_types') ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                            {{ __('Bulk Units & Types') }}
                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="{{ route('setup.bulk_units') }}"
                                            class="ui-nav-sublink {{ request()->routeIs('setup.bulk_units') ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Bulk Units') }}
                                        </a>
                                        <a href="{{ route('setup.bulk_types') }}"
                                            class="ui-nav-sublink {{ request()->routeIs('setup.bulk_types') ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Bulk Types') }}
                                        </a>
                                    </div>
                                </details>
                            @endcan

                            @can('products.manage')
                                <details class="ui-nav-group" {{ request()->routeIs('products.index') ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ request()->routeIs('products.index') ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                            {{ __('Products') }}
                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="{{ route('products.index', ['mode' => 'add']) }}"
                                            class="ui-nav-sublink {{ request()->routeIs('products.index') && request()->route('mode') === 'add' ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Add Product') }}
                                        </a>
                                        <a href="{{ route('products.index', ['mode' => 'manage']) }}"
                                            class="ui-nav-sublink {{ request()->routeIs('products.index') && (request()->route('mode') === 'manage' || request()->route('mode') === null) ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Manage Products') }}
                                        </a>
                                        <a href="{{ route('products.index', ['mode' => 'expired']) }}"
                                            class="ui-nav-sublink {{ request()->routeIs('products.index') && request()->route('mode') === 'expired' ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Expired Products') }}
                                        </a>
                                        @can('opening_stock.manage')
                                            <a href="{{ route('opening_stock.index') }}"
                                                class="ui-nav-sublink {{ request()->routeIs('opening_stock.index') ? 'ui-nav-sublink-active' : '' }}">
                                                {{ __('Opening Stock') }}
                                            </a>
                                        @endcan
                                    </div>
                                </details>
                            @endcan
                        </div>
                    </div>

                    <div class="pt-4">
                        <div class="ui-nav-section-title">{{ __('Operations') }}</div>
                        <div class="mt-2 space-y-1">

                            @can('stock_in.manage')
                                <details class="ui-nav-group" {{ request()->routeIs('stock_in.index') || request()->routeIs('stock_in_records.index') ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ request()->routeIs('stock_in.index') || request()->routeIs('stock_in_records.index') ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            {{ __('Stock In') }}
                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="{{ route('stock_in.index') }}"
                                            class="ui-nav-sublink {{ request()->routeIs('stock_in.index') ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Add Stock In') }}
                                        </a>
                                        @can('stock_in.view')
                                            <a href="{{ route('stock_in_records.index') }}"
                                                class="ui-nav-sublink {{ request()->routeIs('stock_in_records.index') ? 'ui-nav-sublink-active' : '' }}">
                                                {{ __('Stock In Records') }}
                                        </a>
                                        @endcan
                                    </div>
                                </details>
                            @endcan

                            @canany(['stock_levels.view', 'stock_valuation.view', 'batches.view'])
                                <details class="ui-nav-group" {{ request()->routeIs('stock_levels.index') || request()->routeIs('stock_valuation.index') || request()->routeIs('batches.index') ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ request()->routeIs('stock_levels.index') || request()->routeIs('stock_valuation.index') || request()->routeIs('batches.index') ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            {{ __('Stocks') }}
                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        @can('stock_levels.view')
                                            <a href="{{ route('stock_levels.index') }}"
                                                class="ui-nav-sublink {{ request()->routeIs('stock_levels.index') ? 'ui-nav-sublink-active' : '' }}">
                                                {{ __('Stock Levels') }}
                                            </a>
                                        @endcan
                                        @can('stock_valuation.view')
                                            <a href="{{ route('stock_valuation.index') }}"
                                                class="ui-nav-sublink {{ request()->routeIs('stock_valuation.index') ? 'ui-nav-sublink-active' : '' }}">
                                                {{ __('Stock Valuation') }}
                                            </a>
                                        @endcan
                                        @can('batches.view')
                                            <a href="{{ route('batches.index') }}"
                                                class="ui-nav-sublink {{ request()->routeIs('batches.index') ? 'ui-nav-sublink-active' : '' }}">
                                                {{ __('Batch Management') }}
                                            </a>
                                        @endcan
                                    </div>
                                </details>
                            @endcanany

                            @can('stock_adjustments.view')
                                <a href="{{ route('stock_adjustments.index') }}" class="ui-nav-link {{ request()->routeIs('stock_adjustments.index') ? 'ui-nav-link-active' : '' }}">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                        {{ __('Stock Adjustments') }}
                                    </span>
                                </a>
                            @endcan

                            @can('sales.view')
                                <details class="ui-nav-group" {{ request()->routeIs('sales.*') || request()->routeIs('sales_records.*') || request()->routeIs('daily_summary.*') ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ request()->routeIs('sales.*') || request()->routeIs('sales_records.*') || request()->routeIs('daily_summary.*') ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ __('Sales') }}
                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="{{ route('sales.add') }}"
                                            class="ui-nav-sublink {{ request()->routeIs('sales.add') ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Add Sales') }}
                                        </a>
                                        @can('sales_records.view')
                                            <a href="{{ route('sales_records.index') }}"
                                                class="ui-nav-sublink {{ request()->routeIs('sales_records.index') ? 'ui-nav-sublink-active' : '' }}">
                                                {{ __('Sales Records') }}
                                            </a>
                                        @endcan
                                        @can('daily_summary.view')
                                            <a href="{{ route('daily_summary.index') }}"
                                                class="ui-nav-sublink {{ request()->routeIs('daily_summary.index') ? 'ui-nav-sublink-active' : '' }}">
                                                {{ __('Daily Summary') }}
                                            </a>
                                        @endcan
                                    </div>
                                </details>
                            @endcan
                        </div>
                    </div>

                    @can('reports.view')
                        <div class="pt-4">
                            <div class="ui-nav-section-title">{{ __('Analytics') }}</div>
                            <div class="mt-2 space-y-1">
                                <details class="ui-nav-group" {{ request()->routeIs('reports.*') ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ request()->routeIs('reports.*') ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            {{ __('Reports') }}
                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="{{ route('reports.index') }}"
                                            class="ui-nav-sublink {{ request()->routeIs('reports.index') ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Sales') }}
                                        </a>
                                        <a href="{{ route('reports.profit') }}"
                                            class="ui-nav-sublink {{ request()->routeIs('reports.profit') ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Profit') }}
                                        </a>
                                        <a href="{{ route('reports.stock') }}"
                                            class="ui-nav-sublink {{ request()->routeIs('reports.stock') ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Stock') }}
                                        </a>
                                        <a href="{{ route('reports.expenses') }}"
                                            class="ui-nav-sublink {{ request()->routeIs('reports.expenses') ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Expenses') }}
                                        </a>
                                        <a href="{{ route('reports.expiry') }}"
                                            class="ui-nav-sublink {{ request()->routeIs('reports.expiry') ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Expiry') }}
                                        </a>
                                    </div>
                                </details>
                            </div>
                        </div>
                    @endcan

                    @can('rbac.manage')
                        <div class="pt-4">
                            <div class="ui-nav-section-title">{{ __('Settings') }}</div>
                            <div class="mt-2 space-y-1">
                                <details class="ui-nav-group" {{ (request()->routeIs('setup.roles') || request()->routeIs('setup.user_roles')) ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ (request()->routeIs('setup.roles') || request()->routeIs('setup.user_roles')) ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ __('Settings') }}
                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="{{ route('setup.roles') }}"
                                            class="ui-nav-sublink {{ request()->routeIs('setup.roles') ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Roles') }}
                                        </a>
                                        <a href="{{ route('setup.user_roles') }}"
                                            class="ui-nav-sublink {{ request()->routeIs('setup.user_roles') ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('User Roles') }}
                                        </a>
                                    </div>
                                </details>
                            </div>
                        </div>
                    @endcan

                    @canany(['audit.stock_movements.view', 'audit.activity_logs.view'])
                        <div class="pt-4">
                            <div class="ui-nav-section-title">{{ __('Audit') }}</div>
                            <div class="mt-2 space-y-1">
                                <details class="ui-nav-group" {{ (request()->routeIs('stock_movements.index') || request()->routeIs('activity_logs.index')) ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ (request()->routeIs('stock_movements.index') || request()->routeIs('activity_logs.index')) ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                            </svg>
                                            {{ __('Audit Trails') }}
                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        @can('audit.stock_movements.view')
                                            <a href="{{ route('stock_movements.index') }}"
                                                class="ui-nav-sublink {{ request()->routeIs('stock_movements.index') ? 'ui-nav-sublink-active' : '' }}">
                                                {{ __('Stock Movements') }}
                                            </a>
                                        @endcan
                                        @can('audit.activity_logs.view')
                                            <a href="{{ route('activity_logs.index') }}"
                                                class="ui-nav-sublink {{ request()->routeIs('activity_logs.index') ? 'ui-nav-sublink-active' : '' }}">
                                                {{ __('Activity Logs') }}
                                            </a>
                                        @endcan
                                    </div>
                                </details>
                            </div>
                        </div>
                    @endcanany

                    {{-- Notifications --}}
                    @canany(['alerts.low_stock', 'alerts.expiry_warning', 'alerts.expired_stock'])
                        <div class="pt-4">
                            <div class="ui-nav-section-title">{{ __('Alerts') }}</div>
                            <div class="mt-2 space-y-1">
                                <a href="{{ route('notifications.index') }}"
                                    class="ui-nav-link {{ request()->routeIs('notifications.index') ? 'ui-nav-link-active' : '' }}">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        {{ __('Notifications') }}
                                    </span>
                                </a>
                            </div>
                        </div>
                    @endcanany

                    {{-- Expenses Module --}}
                    @canany(['expenses.view', 'expenses.manage'])
                        <div class="pt-4">
                            <div class="ui-nav-section-title">{{ __('Expenses') }}</div>
                            <div class="mt-2 space-y-1">
                                @can('expenses.manage')
                                    <a href="{{ route('expense-types.index') }}"
                                        class="ui-nav-link {{ request()->routeIs('expense-types.index') ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                            {{ __('Expense Types') }}
                                        </span>
                                    </a>
                                @endcan
                                @can('expenses.view')
                                    <a href="{{ route('expenses.index', ['mode' => 'add']) }}"
                                        class="ui-nav-link {{ request()->routeIs('expenses.index') && request()->route('mode') === 'add' ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                            </svg>
                                            {{ __('Add Expense') }}
                                        </span>
                                    </a>
                                @endcan
                                @can('expenses.view')
                                    <a href="{{ route('expenses.index', ['mode' => 'manage']) }}"
                                        class="ui-nav-link {{ request()->routeIs('expenses.index') && request()->route('mode') === 'manage' ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            {{ __('Manage Expenses') }}
                                        </span>
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endcanany

                    {{-- Clearance Module --}}
                    @canany(['clearance.view', 'clearance.discount', 'clearance.donate', 'clearance.dispose', 'clearance.rules.view', 'clearance.reports'])
                        <div class="pt-4">
                            <div class="ui-nav-section-title">{{ __('Clearance') }}</div>
                            <div class="mt-2 space-y-1">
                                @can('clearance.view')
                                    <a href="{{ route('clearance.index') }}"
                                        class="ui-nav-link {{ request()->routeIs('clearance.index') ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                            {{ __('Clearance Manager') }}
                                        </span>
                                    </a>
                                @endcan
                                @can('clearance.records.view')
                                    <a href="{{ route('clearance.records') }}"
                                        class="ui-nav-link {{ request()->routeIs('clearance.records') ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                            {{ __('Clearance Records') }}
                                        </span>
                                    </a>
                                @endcan
                                @can('clearance.rules.view')
                                    <a href="{{ route('clearance.rules') }}"
                                        class="ui-nav-link {{ request()->routeIs('clearance.rules') ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            {{ __('Discount Rules') }}
                                        </span>
                                    </a>
                                @endcan
                                @can('clearance.reports')
                                    <a href="{{ route('clearance.reports') }}"
                                        class="ui-nav-link {{ request()->routeIs('clearance.reports') ? 'ui-nav-link-active' : '' }}">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            {{ __('Reports') }}
                                        </span>
                                    </a>
                                @endcan
                            </div>
                        </div>
                    @endcanany
                </nav>
            </aside>

            <div class="flex-1 min-w-0">
                <div class="ui-topbar">
                    <div class="ui-topbar-inner">
                        <div class="hidden md:flex md:items-center">
                            <div class="ui-breadcrumb">
                                <span class="ui-breadcrumb-item">{{ config('app.name') }}</span>
                                <span class="ui-breadcrumb-sep">/</span>
                                <span class="ui-breadcrumb-current">
                                    {{ \Illuminate\Support\Str::of((string) (request()->route()?->getName() ?? ''))->replace('.', ' ')->title() ?: __('Dashboard') }}
                                </span>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center gap-3">
                            @canany(['alerts.stock_adjustment', 'alerts.expired_stock', 'alerts.expiry_warning', 'alerts.low_stock'])
                                <div wire:ignore>
                                    <x-notification-bell />
                                </div>
                            @endcanany

                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="ui-user-trigger">
                                        <div>{{ Auth::user()->name }}</div>

                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                </x-slot>

                                <x-slot name="content">
                                    <x-dropdown-link :href="route('profile.edit')">
                                        {{ __('Profile') }}
                                    </x-dropdown-link>

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf

                                        <x-dropdown-link :href="route('logout')"
                                                onclick="event.preventDefault();
                                                            this.closest('form').submit();">
                                            {{ __('Log Out') }}
                                        </x-dropdown-link>
                                    </form>
                                </x-slot>
                            </x-dropdown>
                        </div>
                    </div>
                </div>

                <!-- Page Heading -->
                @isset($header)
                    <header class="ui-page-header">
                        <div class="ui-page-container py-6">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <!-- Page Content -->
                <main>
                    {{ $slot }}
                </main>
            </div>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
