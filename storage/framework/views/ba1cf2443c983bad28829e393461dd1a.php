<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title><?php echo e(config('app.name', 'Retail_Sm')); ?></title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>

        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

    </head>
    <body class="font-sans antialiased">
        <div class="ui-shell">
            <div class="ui-shell-inner">
            <aside class="ui-sidebar">
                <div class="ui-sidebar-header">
                    <a href="<?php echo e(route('dashboard')); ?>" class="ui-sidebar-brand">
                        <?php echo e(config('app.name')); ?>

                    </a>
                </div>

                <nav class="ui-nav">
                    <a href="<?php echo e(route('dashboard')); ?>"
                        class="ui-nav-link <?php echo e(request()->routeIs('dashboard') ? 'ui-nav-link-active' : ''); ?>">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <?php echo e(__('Dashboard')); ?>

                        </span>
                    </a>

                    <div class="pt-2">
                        <div class="ui-nav-section-title"><?php echo e(__('Setup')); ?></div>
                        <div class="mt-2 space-y-1">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('branches.manage')): ?>
                                <a href="<?php echo e(route('setup.branches')); ?>"
                                    class="ui-nav-link <?php echo e(request()->routeIs('setup.branches') ? 'ui-nav-link-active' : ''); ?>">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <?php echo e(__('Branches')); ?>

                                    </span>
                                </a>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('users.manage')): ?>
                                <a href="<?php echo e(route('users.index')); ?>"
                                    class="ui-nav-link <?php echo e(request()->routeIs('users.*') ? 'ui-nav-link-active' : ''); ?>">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                        <?php echo e(__('Users')); ?>

                                    </span>
                                </a>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setup.categories.manage')): ?>
                                <a href="<?php echo e(route('setup.categories')); ?>"
                                    class="ui-nav-link <?php echo e(request()->routeIs('setup.categories') ? 'ui-nav-link-active' : ''); ?>">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        <?php echo e(__('Categories')); ?>

                                    </span>
                                </a>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setup.unit_types.manage')): ?>
                                <a href="<?php echo e(route('setup.unit_types')); ?>"
                                    class="ui-nav-link <?php echo e(request()->routeIs('setup.unit_types') ? 'ui-nav-link-active' : ''); ?>">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9m6-9l-3 9m3-9l3 1m-3-1l3-9a5.002 5.002 0 00-6.001 0M18 7l3 9a5.002 5.002 0 01-6.001 0M3 6l3 1m0 0l3 9m6-9l-3 9m6-9l3 1" />
                                        </svg>
                                        <?php echo e(__('Unit Types')); ?>

                                    </span>
                                </a>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('setup.bulk.manage')): ?>
                                <details class="ui-nav-group" <?php echo e(request()->routeIs('setup.bulk_units') || request()->routeIs('setup.bulk_types') ? 'open' : ''); ?>>
                                    <summary class="ui-nav-group-summary <?php echo e(request()->routeIs('setup.bulk_units') || request()->routeIs('setup.bulk_types') ? 'ui-nav-link-active' : ''); ?>">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                            <?php echo e(__('Bulk Units & Types')); ?>

                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="<?php echo e(route('setup.bulk_units')); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('setup.bulk_units') ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Bulk Units')); ?>

                                        </a>
                                        <a href="<?php echo e(route('setup.bulk_types')); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('setup.bulk_types') ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Bulk Types')); ?>

                                        </a>
                                    </div>
                                </details>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('products.manage')): ?>
                                <details class="ui-nav-group" <?php echo e(request()->routeIs('products.index') ? 'open' : ''); ?>>
                                    <summary class="ui-nav-group-summary <?php echo e(request()->routeIs('products.index') ? 'ui-nav-link-active' : ''); ?>">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                            </svg>
                                            <?php echo e(__('Products')); ?>

                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="<?php echo e(route('products.index', ['mode' => 'add'])); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('products.index') && request()->route('mode') === 'add' ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Add Product')); ?>

                                        </a>
                                        <a href="<?php echo e(route('products.index', ['mode' => 'manage'])); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('products.index') && (request()->route('mode') === 'manage' || request()->route('mode') === null) ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Manage Products')); ?>

                                        </a>
                                        <a href="<?php echo e(route('products.index', ['mode' => 'expired'])); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('products.index') && request()->route('mode') === 'expired' ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Expired Products')); ?>

                                        </a>
                                    </div>
                                </details>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="pt-4">
                        <div class="ui-nav-section-title"><?php echo e(__('Operations')); ?></div>
                        <div class="mt-2 space-y-1">

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('stock_in.manage')): ?>
                                <details class="ui-nav-group" <?php echo e(request()->routeIs('stock_in.index') ? 'open' : ''); ?>>
                                    <summary class="ui-nav-group-summary <?php echo e(request()->routeIs('stock_in.index') ? 'ui-nav-link-active' : ''); ?>">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <?php echo e(__('Stock In')); ?>

                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="<?php echo e(route('stock_in.index', ['mode' => 'add'])); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('stock_in.index') && request()->route('mode') === 'add' ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Add Stock In')); ?>

                                        </a>
                                        <a href="<?php echo e(route('stock_in.index', ['mode' => 'manage'])); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('stock_in.index') && (request()->route('mode') === 'manage' || request()->route('mode') === null) ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Manage Stock In')); ?>

                                        </a>
                                    </div>
                                </details>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('sales.manage')): ?>
                                <details class="ui-nav-group" <?php echo e(request()->routeIs('sales.*') ? 'open' : ''); ?>>
                                    <summary class="ui-nav-group-summary <?php echo e(request()->routeIs('sales.*') ? 'ui-nav-link-active' : ''); ?>">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <?php echo e(__('Sales')); ?>

                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="<?php echo e(route('sales.add')); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('sales.add') ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Add Sales')); ?>

                                        </a>
                                        <a href="<?php echo e(route('sales.manage')); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('sales.manage') ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Manage Sales')); ?>

                                        </a>
                                    </div>
                                </details>
                            <?php endif; ?>

                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('expenses.manage')): ?>
                                <details class="ui-nav-group" <?php echo e(request()->routeIs('expenses.index') ? 'open' : ''); ?>>
                                    <summary class="ui-nav-group-summary <?php echo e(request()->routeIs('expenses.index') ? 'ui-nav-link-active' : ''); ?>">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                            <?php echo e(__('Expenses')); ?>

                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="<?php echo e(route('expenses.index', ['mode' => 'add'])); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('expenses.index') && request()->route('mode') === 'add' ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Add Expense')); ?>

                                        </a>
                                        <a href="<?php echo e(route('expenses.index', ['mode' => 'manage'])); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('expenses.index') && (request()->route('mode') === 'manage' || request()->route('mode') === null) ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Manage Expenses')); ?>

                                        </a>
                                    </div>
                                </details>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('reports.view')): ?>
                        <div class="pt-4">
                            <div class="ui-nav-section-title"><?php echo e(__('Analytics')); ?></div>
                            <div class="mt-2 space-y-1">
                                <details class="ui-nav-group" <?php echo e(request()->routeIs('reports.*') ? 'open' : ''); ?>>
                                    <summary class="ui-nav-group-summary <?php echo e(request()->routeIs('reports.*') ? 'ui-nav-link-active' : ''); ?>">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            <?php echo e(__('Reports')); ?>

                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="<?php echo e(route('reports.index')); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('reports.index') ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Sales')); ?>

                                        </a>
                                        <a href="<?php echo e(route('reports.profit')); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('reports.profit') ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Profit')); ?>

                                        </a>
                                        <a href="<?php echo e(route('reports.stock')); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('reports.stock') ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Stock')); ?>

                                        </a>
                                        <a href="<?php echo e(route('reports.expenses')); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('reports.expenses') ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Expenses')); ?>

                                        </a>
                                        <a href="<?php echo e(route('reports.expiry')); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('reports.expiry') ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Expiry')); ?>

                                        </a>
                                    </div>
                                </details>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('rbac.manage')): ?>
                        <div class="pt-4">
                            <div class="ui-nav-section-title"><?php echo e(__('Settings')); ?></div>
                            <div class="mt-2 space-y-1">
                                <details class="ui-nav-group" <?php echo e((request()->routeIs('setup.roles') || request()->routeIs('setup.user_roles')) ? 'open' : ''); ?>>
                                    <summary class="ui-nav-group-summary <?php echo e((request()->routeIs('setup.roles') || request()->routeIs('setup.user_roles')) ? 'ui-nav-link-active' : ''); ?>">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <?php echo e(__('Settings')); ?>

                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <a href="<?php echo e(route('setup.roles')); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('setup.roles') ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('Roles')); ?>

                                        </a>
                                        <a href="<?php echo e(route('setup.user_roles')); ?>"
                                            class="ui-nav-sublink <?php echo e(request()->routeIs('setup.user_roles') ? 'ui-nav-sublink-active' : ''); ?>">
                                            <?php echo e(__('User Roles')); ?>

                                        </a>
                                    </div>
                                </details>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['audit.stock_movements.view', 'audit.activity_logs.view'])): ?>
                        <div class="pt-4">
                            <div class="ui-nav-section-title"><?php echo e(__('Audit')); ?></div>
                            <div class="mt-2 space-y-1">
                                <details class="ui-nav-group" <?php echo e((request()->routeIs('stock_movements.index') || request()->routeIs('activity_logs.index')) ? 'open' : ''); ?>>
                                    <summary class="ui-nav-group-summary <?php echo e((request()->routeIs('stock_movements.index') || request()->routeIs('activity_logs.index')) ? 'ui-nav-link-active' : ''); ?>">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                            </svg>
                                            <?php echo e(__('Audit Trails')); ?>

                                        </span>
                                        <svg class="ui-nav-caret" width="16" height="16" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </summary>
                                    <div class="ui-nav-group-panel">
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('audit.stock_movements.view')): ?>
                                            <a href="<?php echo e(route('stock_movements.index')); ?>"
                                                class="ui-nav-sublink <?php echo e(request()->routeIs('stock_movements.index') ? 'ui-nav-sublink-active' : ''); ?>">
                                                <?php echo e(__('Stock Movements')); ?>

                                            </a>
                                        <?php endif; ?>
                                        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('audit.activity_logs.view')): ?>
                                            <a href="<?php echo e(route('activity_logs.index')); ?>"
                                                class="ui-nav-sublink <?php echo e(request()->routeIs('activity_logs.index') ? 'ui-nav-sublink-active' : ''); ?>">
                                                <?php echo e(__('Activity Logs')); ?>

                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </details>
                            </div>
                        </div>
                    <?php endif; ?>

                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['alerts.low_stock', 'alerts.expiry_warning', 'alerts.expired_stock'])): ?>
                        <div class="pt-4">
                            <div class="ui-nav-section-title"><?php echo e(__('Alerts')); ?></div>
                            <div class="mt-2 space-y-1">
                                <a href="<?php echo e(route('notifications.index')); ?>"
                                    class="ui-nav-link <?php echo e(request()->routeIs('notifications.index') ? 'ui-nav-link-active' : ''); ?>">
                                    <span class="flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        <?php echo e(__('Notifications')); ?>

                                    </span>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    
                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['clearance.view', 'clearance.discount', 'clearance.donate', 'clearance.dispose', 'clearance.rules.view', 'clearance.reports'])): ?>
                        <div class="pt-4">
                            <div class="ui-nav-section-title"><?php echo e(__('Clearance')); ?></div>
                            <div class="mt-2 space-y-1">
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clearance.view')): ?>
                                    <a href="<?php echo e(route('clearance.index')); ?>"
                                        class="ui-nav-link <?php echo e(request()->routeIs('clearance.index') ? 'ui-nav-link-active' : ''); ?>">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                            </svg>
                                            <?php echo e(__('Clearance Manager')); ?>

                                        </span>
                                    </a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clearance.rules.view')): ?>
                                    <a href="<?php echo e(route('clearance.rules')); ?>"
                                        class="ui-nav-link <?php echo e(request()->routeIs('clearance.rules') ? 'ui-nav-link-active' : ''); ?>">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            </svg>
                                            <?php echo e(__('Discount Rules')); ?>

                                        </span>
                                    </a>
                                <?php endif; ?>
                                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clearance.reports')): ?>
                                    <a href="<?php echo e(route('clearance.reports')); ?>"
                                        class="ui-nav-link <?php echo e(request()->routeIs('clearance.reports') ? 'ui-nav-link-active' : ''); ?>">
                                        <span class="flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                            </svg>
                                            <?php echo e(__('Reports')); ?>

                                        </span>
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </nav>
            </aside>

            <div class="flex-1 min-w-0">
                <div class="ui-topbar">
                    <div class="ui-topbar-inner">
                        <div class="hidden md:flex md:items-center">
                            <div class="ui-breadcrumb">
                                <span class="ui-breadcrumb-item"><?php echo e(config('app.name')); ?></span>
                                <span class="ui-breadcrumb-sep">/</span>
                                <span class="ui-breadcrumb-current">
                                    <?php echo e(\Illuminate\Support\Str::of((string) (request()->route()?->getName() ?? ''))->replace('.', ' ')->title() ?: __('Dashboard')); ?>

                                </span>
                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center gap-3">
                            <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['alerts.stock_adjustment', 'alerts.expired_stock', 'alerts.expiry_warning', 'alerts.low_stock'])): ?>
                                <?php if (isset($component)) { $__componentOriginal6256a41827a60bb4a6fb9d463f328406 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal6256a41827a60bb4a6fb9d463f328406 = $attributes; } ?>
<?php $component = App\View\Components\NotificationBell::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('notification-bell'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\NotificationBell::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal6256a41827a60bb4a6fb9d463f328406)): ?>
<?php $attributes = $__attributesOriginal6256a41827a60bb4a6fb9d463f328406; ?>
<?php unset($__attributesOriginal6256a41827a60bb4a6fb9d463f328406); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal6256a41827a60bb4a6fb9d463f328406)): ?>
<?php $component = $__componentOriginal6256a41827a60bb4a6fb9d463f328406; ?>
<?php unset($__componentOriginal6256a41827a60bb4a6fb9d463f328406); ?>
<?php endif; ?>
                            <?php endif; ?>

                            <?php if (isset($component)) { $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown','data' => ['align' => 'right','width' => '48']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['align' => 'right','width' => '48']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                 <?php $__env->slot('trigger', null, []); ?> 
                                    <button class="ui-user-trigger">
                                        <div><?php echo e(Auth::user()->name); ?></div>

                                        <div class="ms-1">
                                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>
                                 <?php $__env->endSlot(); ?>

                                 <?php $__env->slot('content', null, []); ?> 
                                    <?php if (isset($component)) { $__componentOriginal68cb1971a2b92c9735f83359058f7108 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68cb1971a2b92c9735f83359058f7108 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-link','data' => ['href' => route('profile.edit')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('profile.edit'))]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                        <?php echo e(__('Profile')); ?>

                                     <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $attributes = $__attributesOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__attributesOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $component = $__componentOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__componentOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>

                                    <form method="POST" action="<?php echo e(route('logout')); ?>">
                                        <?php echo csrf_field(); ?>

                                        <?php if (isset($component)) { $__componentOriginal68cb1971a2b92c9735f83359058f7108 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal68cb1971a2b92c9735f83359058f7108 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.dropdown-link','data' => ['href' => route('logout'),'onclick' => 'event.preventDefault();
                                                            this.closest(\'form\').submit();']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('dropdown-link'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['href' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute(route('logout')),'onclick' => 'event.preventDefault();
                                                            this.closest(\'form\').submit();']); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

                                            <?php echo e(__('Log Out')); ?>

                                         <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $attributes = $__attributesOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__attributesOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal68cb1971a2b92c9735f83359058f7108)): ?>
<?php $component = $__componentOriginal68cb1971a2b92c9735f83359058f7108; ?>
<?php unset($__componentOriginal68cb1971a2b92c9735f83359058f7108); ?>
<?php endif; ?>
                                    </form>
                                 <?php $__env->endSlot(); ?>
                             <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $attributes = $__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__attributesOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
<?php if (isset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe)): ?>
<?php $component = $__componentOriginaldf8083d4a852c446488d8d384bbc7cbe; ?>
<?php unset($__componentOriginaldf8083d4a852c446488d8d384bbc7cbe); ?>
<?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Page Heading -->
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(isset($header)): ?>
                    <header class="ui-page-header">
                        <div class="ui-page-container py-6">
                            <?php echo e($header); ?>

                        </div>
                    </header>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <!-- Page Content -->
                <main>
                    <?php echo e($slot); ?>

                </main>
            </div>
            </div>
        </div>

        <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

    </body>
</html>
<?php /**PATH C:\xampp\htdocs\Cshop\resources\views/layouts/app.blade.php ENDPATH**/ ?>