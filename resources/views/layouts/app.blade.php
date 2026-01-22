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
        <div class="min-h-screen bg-gradient-to-b from-slate-50 to-slate-100 flex">
            <aside class="w-64 bg-white/90 backdrop-blur border-r border-gray-200 hidden md:flex md:flex-col">
                <div class="h-16 px-6 flex items-center border-b border-gray-100">
                    <a href="{{ route('dashboard') }}" class="text-lg font-semibold text-gray-900 tracking-tight">
                        {{ config('app.name') }}
                    </a>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-1">
                    <a href="{{ route('dashboard') }}"
                        class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('dashboard') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                        {{ __('Dashboard') }}
                    </a>

                    <div class="pt-2">
                        <div class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Setup') }}</div>
                        <div class="mt-2 space-y-1">
                            @if (auth()->user() && auth()->user()->role === 'super_admin')
                                <a href="{{ route('setup.branches') }}"
                                    class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('setup.branches') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                                    {{ __('Branches') }}
                                </a>

                                <a href="{{ route('users.index') }}"
                                    class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('users.*') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                                    {{ __('Users') }}
                                </a>
                            @endif

                            <a href="{{ route('setup.categories') }}"
                                class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('setup.categories') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                                {{ __('Categories') }}
                            </a>

                            <a href="{{ route('setup.bulk_units') }}"
                                class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('setup.bulk_units') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                                {{ __('Bulk Units') }}
                            </a>

                            <a href="{{ route('setup.bulk_types') }}"
                                class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('setup.bulk_types') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                                {{ __('Bulk Types') }}
                            </a>

                            <a href="{{ route('products.index') }}"
                                class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('products.index') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                                {{ __('Products') }}
                            </a>
                        </div>
                    </div>

                    <div class="pt-4">
                        <div class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Operations') }}</div>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('stock_in.index') }}"
                                class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('stock_in.index') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                                {{ __('Stock In') }}
                            </a>

                            <a href="{{ route('sales.index') }}"
                                class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('sales.index') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                                {{ __('Sales') }}
                            </a>

                            <a href="{{ route('stock_movements.index') }}"
                                class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('stock_movements.index') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                                {{ __('Stock Movements') }}
                            </a>
                        </div>
                    </div>

                    <div class="pt-4">
                        <div class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{ __('Analytics') }}</div>
                        <div class="mt-2 space-y-1">
                            <a href="{{ route('reports.index') }}"
                                class="block px-3 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('reports.index') ? 'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-100' : 'text-gray-700 hover:bg-gray-50 hover:text-gray-900' }}">
                                {{ __('Reports') }}
                            </a>
                        </div>
                    </div>
                </nav>
            </aside>

            <div class="flex-1 min-w-0">
                <div class="bg-white/80 backdrop-blur border-b border-gray-200">
                    <div class="h-16 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-end">
                        <div class="hidden sm:flex sm:items-center">
                            <x-dropdown align="right" width="48">
                                <x-slot name="trigger">
                                    <button class="inline-flex items-center px-3 py-2 text-sm font-semibold rounded-lg text-gray-700 bg-white/50 hover:bg-white hover:text-gray-900 ring-1 ring-inset ring-gray-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
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
                    <header class="bg-white shadow">
                        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
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

        @livewireScripts
    </body>
</html>
