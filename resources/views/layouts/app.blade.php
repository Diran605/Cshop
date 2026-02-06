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
                        {{ __('Dashboard') }}
                    </a>

                    <div class="pt-2">
                        <div class="ui-nav-section-title">{{ __('Setup') }}</div>
                        <div class="mt-2 space-y-1">
                            @can('branches.manage')
                                <a href="{{ route('setup.branches') }}"
                                    class="ui-nav-link {{ request()->routeIs('setup.branches') ? 'ui-nav-link-active' : '' }}">
                                    {{ __('Branches') }}
                                </a>
                            @endcan

                            @can('users.manage')
                                <a href="{{ route('users.index') }}"
                                    class="ui-nav-link {{ request()->routeIs('users.*') ? 'ui-nav-link-active' : '' }}">
                                    {{ __('Users') }}
                                </a>
                            @endcan

                            @can('rbac.manage')
                                <details class="ui-nav-group" {{ (request()->routeIs('setup.roles') || request()->routeIs('setup.user_roles')) ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ (request()->routeIs('setup.roles') || request()->routeIs('setup.user_roles')) ? 'ui-nav-link-active' : '' }}">
                                        <span>{{ __('Settings') }}</span>
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
                            @endcan

                            @can('setup.categories.manage')
                                <a href="{{ route('setup.categories') }}"
                                    class="ui-nav-link {{ request()->routeIs('setup.categories') ? 'ui-nav-link-active' : '' }}">
                                    {{ __('Categories') }}
                                </a>
                            @endcan

                            @can('setup.bulk.manage')
                                <details class="ui-nav-group" {{ request()->routeIs('setup.bulk_units') || request()->routeIs('setup.bulk_types') ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ request()->routeIs('setup.bulk_units') || request()->routeIs('setup.bulk_types') ? 'ui-nav-link-active' : '' }}">
                                        <span>{{ __('Bulk Units & Types') }}</span>
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
                                        <span>{{ __('Products') }}</span>
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
                                    </div>
                                </details>
                            @endcan
                        </div>
                    </div>

                    <div class="pt-4">
                        <div class="ui-nav-section-title">{{ __('Operations') }}</div>
                        <div class="mt-2 space-y-1">

                            @can('stock_in.manage')
                                <details class="ui-nav-group" {{ request()->routeIs('stock_in.index') ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ request()->routeIs('stock_in.index') ? 'ui-nav-link-active' : '' }}">
                                        <span>{{ __('Stock In') }}</span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="{{ route('stock_in.index', ['mode' => 'add']) }}"
                                            class="ui-nav-sublink {{ request()->routeIs('stock_in.index') && request()->route('mode') === 'add' ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Add Stock In') }}
                                        </a>
                                        <a href="{{ route('stock_in.index', ['mode' => 'manage']) }}"
                                            class="ui-nav-sublink {{ request()->routeIs('stock_in.index') && (request()->route('mode') === 'manage' || request()->route('mode') === null) ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Manage Stock In') }}
                                        </a>
                                    </div>
                                </details>
                            @endcan

                            @can('sales.manage')
                                <details class="ui-nav-group" {{ request()->routeIs('sales.*') ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ request()->routeIs('sales.*') ? 'ui-nav-link-active' : '' }}">
                                        <span>{{ __('Sales') }}</span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="{{ route('sales.add') }}"
                                            class="ui-nav-sublink {{ request()->routeIs('sales.add') ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Add Sales') }}
                                        </a>
                                        <a href="{{ route('sales.manage') }}"
                                            class="ui-nav-sublink {{ request()->routeIs('sales.manage') ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Manage Sales') }}
                                        </a>
                                    </div>
                                </details>
                            @endcan

                            @can('expenses.manage')
                                <details class="ui-nav-group" {{ request()->routeIs('expenses.index') ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ request()->routeIs('expenses.index') ? 'ui-nav-link-active' : '' }}">
                                        <span>{{ __('Expenses') }}</span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="{{ route('expenses.index', ['mode' => 'add']) }}"
                                            class="ui-nav-sublink {{ request()->routeIs('expenses.index') && request()->route('mode') === 'add' ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Add Expense') }}
                                        </a>
                                        <a href="{{ route('expenses.index', ['mode' => 'manage']) }}"
                                            class="ui-nav-sublink {{ request()->routeIs('expenses.index') && (request()->route('mode') === 'manage' || request()->route('mode') === null) ? 'ui-nav-sublink-active' : '' }}">
                                            {{ __('Manage Expenses') }}
                                        </a>
                                    </div>
                                </details>
                            @endcan

                            @canany(['audit.stock_movements.view', 'audit.activity_logs.view'])
                                <details class="ui-nav-group" {{ (request()->routeIs('stock_movements.index') || request()->routeIs('activity_logs.index')) ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ (request()->routeIs('stock_movements.index') || request()->routeIs('activity_logs.index')) ? 'ui-nav-link-active' : '' }}">
                                        <span>{{ __('Audit Trails') }}</span>
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
                            @endcanany
                        </div>
                    </div>

                    @can('reports.view')
                        <div class="pt-4">
                            <div class="ui-nav-section-title">{{ __('Analytics') }}</div>
                            <div class="mt-2 space-y-1">
                                <details class="ui-nav-group" {{ request()->routeIs('reports.*') ? 'open' : '' }}>
                                    <summary class="ui-nav-group-summary {{ request()->routeIs('reports.*') ? 'ui-nav-link-active' : '' }}">
                                        <span>{{ __('Reports') }}</span>
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

                        <div class="hidden sm:flex sm:items-center">
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
