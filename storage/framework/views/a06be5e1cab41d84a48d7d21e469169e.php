<div class="ui-page">
    <div class="ui-page-container">
        
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4"><?php echo e(__("Today's Overview")); ?></h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <div class="ui-kpi-card">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="ui-kpi-title"><?php echo e(__('Total Sales')); ?></div>
                            <div class="ui-kpi-value">XAF <?php echo e(number_format($this->today_stats['sales'], 0, ',', ' ')); ?></div>
                        </div>
                        <div class="flex items-center gap-1 <?php echo e($this->today_stats['sales_change'] >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->today_stats['sales_change'] >= 0): ?>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                            <?php else: ?>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <span class="text-sm font-medium"><?php echo e(abs($this->today_stats['sales_change'])); ?>%</span>
                        </div>
                    </div>
                    <div class="text-xs text-slate-500 mt-1"><?php echo e(__('vs yesterday')); ?></div>
                </div>

                
                <div class="ui-kpi-card">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="ui-kpi-title"><?php echo e(__('Profit')); ?></div>
                            <div class="ui-kpi-value">XAF <?php echo e(number_format($this->today_stats['profit'], 0, ',', ' ')); ?></div>
                        </div>
                        <div class="flex items-center gap-1 <?php echo e($this->today_stats['profit_change'] >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->today_stats['profit_change'] >= 0): ?>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                            <?php else: ?>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <span class="text-sm font-medium"><?php echo e(abs($this->today_stats['profit_change'])); ?>%</span>
                        </div>
                    </div>
                    <div class="text-xs text-slate-500 mt-1"><?php echo e(__('vs yesterday')); ?></div>
                </div>

                
                <div class="ui-kpi-card">
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="ui-kpi-title"><?php echo e(__('Transactions')); ?></div>
                            <div class="ui-kpi-value"><?php echo e($this->today_stats['transactions']); ?></div>
                        </div>
                        <div class="flex items-center gap-1 <?php echo e($this->today_stats['transactions_change'] >= 0 ? 'text-green-600' : 'text-red-600'); ?>">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->today_stats['transactions_change'] >= 0): ?>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                </svg>
                            <?php else: ?>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                </svg>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <span class="text-sm font-medium"><?php echo e(abs($this->today_stats['transactions_change'])); ?>%</span>
                        </div>
                    </div>
                    <div class="text-xs text-slate-500 mt-1"><?php echo e(__('vs yesterday')); ?></div>
                </div>
            </div>
        </div>

        
        <div class="mb-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-4 border border-blue-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-blue-600 font-medium"><?php echo e(__('Stock Value')); ?></div>
                        <div class="text-lg font-bold text-blue-900">XAF <?php echo e(number_format($this->stock_stats['value'], 0, ',', ' ')); ?></div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-4 border border-purple-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-purple-600 font-medium"><?php echo e(__('Stock Items')); ?></div>
                        <div class="text-lg font-bold text-purple-900"><?php echo e($this->stock_stats['items']); ?></div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-amber-50 to-amber-100 rounded-xl p-4 border border-amber-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-amber-600 font-medium"><?php echo e(__('Low Stock Items')); ?></div>
                        <div class="text-lg font-bold text-amber-900"><?php echo e($this->stock_stats['low_stock_count']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-4"><?php echo e(__('Quick Actions')); ?></h2>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <a href="<?php echo e(route('sales.index')); ?>" class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-blue hover:shadow-md transition-all group">
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center group-hover:bg-green-200 transition-colors">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700"><?php echo e(__('New Sale')); ?></span>
                </a>

                <a href="<?php echo e(route('stock_in.index')); ?>" class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-blue hover:shadow-md transition-all group">
                    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center group-hover:bg-blue-200 transition-colors">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-10 11h6" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700"><?php echo e(__('Add Stock')); ?></span>
                </a>

                <a href="<?php echo e(route('products.index')); ?>" class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-blue hover:shadow-md transition-all group">
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center group-hover:bg-purple-200 transition-colors">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700"><?php echo e(__('New Product')); ?></span>
                </a>

                <a href="<?php echo e(route('reports.index')); ?>" class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-blue hover:shadow-md transition-all group">
                    <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center group-hover:bg-amber-200 transition-colors">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <span class="text-sm font-medium text-slate-700"><?php echo e(__('View Reports')); ?></span>
                </a>
            </div>
        </div>

        
        <div class="mb-6 grid grid-cols-1 <?php echo e($this->hasClearancePermission ? 'lg:grid-cols-4' : 'lg:grid-cols-3'); ?> gap-6">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->hasClearancePermission): ?>
                
                <div class="ui-card bg-orange-50 border-orange-200">
                    <div class="ui-card-body">
                        <div class="flex items-center gap-2 mb-3">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-orange-900"><?php echo e(__('Clearance Items')); ?></h3>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->clearanceCount > 0): ?>
                            <div class="text-center py-2">
                                <div class="text-2xl font-bold text-orange-700"><?php echo e($this->clearanceCount); ?></div>
                                <div class="text-xs text-orange-600"><?php echo e(__('items need action')); ?></div>
                            </div>
                            <a href="<?php echo e(route('clearance.index')); ?>" class="mt-2 block text-center text-xs font-medium text-orange-700 hover:text-orange-800">
                                <?php echo e(__('View Clearance Manager')); ?> →
                            </a>
                        <?php else: ?>
                            <div class="text-sm text-orange-600 text-center py-2"><?php echo e(__('No clearance items pending')); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-900"><?php echo e(__('Low Stock Items')); ?></h3>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->low_stock_items->count() > 0): ?>
                        <div class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->low_stock_items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stock): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <div class="flex items-center justify-between p-2 bg-red-50 rounded-lg">
                                    <span class="text-sm text-slate-700"><?php echo e($stock->product?->name ?? '-'); ?></span>
                                    <span class="text-xs font-medium text-red-600"><?php echo e($stock->current_stock); ?> left</span>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-slate-500 text-center py-4"><?php echo e(__('All items are well stocked')); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-900"><?php echo e(__('Expiring Soon')); ?></h3>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->expiring_products->count() > 0): ?>
                        <div class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->expiring_products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stock): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <div class="flex items-center justify-between p-2 bg-amber-50 rounded-lg">
                                    <span class="text-sm text-slate-700"><?php echo e($stock->product_name); ?></span>
                                    <span class="text-xs font-medium text-amber-600"><?php echo e(Carbon\Carbon::parse($stock->expiry_date)->diffForHumans()); ?></span>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-slate-500 text-center py-4"><?php echo e(__('No items expiring soon')); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-semibold text-slate-900"><?php echo e(__('Recent Activity')); ?></h3>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->recent_activity->count() > 0): ?>
                        <div class="space-y-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->recent_activity; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <div class="flex items-center justify-between p-2 bg-slate-50 rounded-lg">
                                    <div>
                                        <div class="text-sm text-slate-700"><?php echo e($activity['description']); ?></div>
                                        <div class="text-xs text-slate-500"><?php echo e($activity['user']); ?></div>
                                    </div>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activity['amount']): ?>
                                        <span class="text-xs font-medium text-green-600">XAF <?php echo e(number_format($activity['amount'], 0, ',', ' ')); ?></span>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-slate-500 text-center py-4"><?php echo e(__('No recent activity')); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="mb-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title"><?php echo e(__('Sales Trend (7 Days)')); ?></h3>
                    <div class="mt-4">
                        <div class="flex items-end justify-between h-40 gap-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->sales_trend['labels']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <?php
                                    $value = $this->sales_trend['data'][$i];
                                    $max = max($this->sales_trend['data']) ?: 1;
                                    $height = $max > 0 ? ($value / $max) * 100 : 0;
                                ?>
                                <div class="flex-1 flex flex-col items-center gap-1">
                                    <div class="text-xs text-slate-500">XAF <?php echo e(number_format($value, 0, ',', ' ')); ?></div>
                                    <div class="w-full bg-primary-blue/20 rounded-t relative" style="height: <?php echo e(max($height, 4)); ?>%">
                                        <div class="absolute inset-0 bg-primary-blue rounded-t opacity-80 hover:opacity-100 transition-opacity"></div>
                                    </div>
                                    <div class="text-xs text-slate-500"><?php echo e($label); ?></div>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title"><?php echo e(__('Top Products This Week')); ?></h3>
                    <div class="mt-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->top_products->count() > 0): ?>
                            <div class="space-y-3">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->top_products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <div class="flex items-center gap-3">
                                        <div class="w-6 h-6 rounded-full bg-primary-blue text-white text-xs flex items-center justify-center font-medium">
                                            <?php echo e($i + 1); ?>

                                        </div>
                                        <div class="flex-1">
                                            <div class="text-sm font-medium text-slate-900"><?php echo e($product->name); ?></div>
                                            <div class="text-xs text-slate-500"><?php echo e($product->qty_sold); ?> sold</div>
                                        </div>
                                        <div class="text-sm font-semibold text-green-600">XAF <?php echo e(number_format($product->revenue, 0, ',', ' ')); ?></div>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-sm text-slate-500 text-center py-8"><?php echo e(__('No sales data for this week')); ?></div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title"><?php echo e(__('Profit Summary (This Month)')); ?></h3>
                    <div class="mt-4 space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-sm text-slate-600"><?php echo e(__('Gross Revenue')); ?></span>
                            <span class="text-sm font-semibold text-slate-900">XAF <?php echo e(number_format($this->profit_summary['gross_revenue'], 0, ',', ' ')); ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-sm text-slate-600"><?php echo e(__('Cost of Goods Sold')); ?></span>
                            <span class="text-sm font-semibold text-red-600">-XAF <?php echo e(number_format($this->profit_summary['cogs'], 0, ',', ' ')); ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-sm text-slate-600"><?php echo e(__('Gross Profit')); ?></span>
                            <div class="text-right">
                                <div class="text-sm font-semibold text-green-600">XAF <?php echo e(number_format($this->profit_summary['gross_profit'], 0, ',', ' ')); ?></div>
                                <div class="text-xs text-slate-500"><?php echo e($this->profit_summary['gross_margin']); ?>% margin</div>
                            </div>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-slate-100">
                            <span class="text-sm text-slate-600"><?php echo e(__('Operating Expenses')); ?></span>
                            <span class="text-sm font-semibold text-red-600">-XAF <?php echo e(number_format($this->profit_summary['expenses'], 0, ',', ' ')); ?></span>
                        </div>
                        <div class="flex justify-between items-center py-2 bg-green-50 rounded-lg px-3 -mx-3">
                            <span class="text-sm font-semibold text-slate-900"><?php echo e(__('Net Profit')); ?></span>
                            <div class="text-right">
                                <div class="text-lg font-bold text-green-600">XAF <?php echo e(number_format($this->profit_summary['net_profit'], 0, ',', ' ')); ?></div>
                                <div class="text-xs text-slate-500"><?php echo e($this->profit_summary['net_margin']); ?>% margin</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="ui-card-title mb-0"><?php echo e(__('Recent Sales')); ?></h3>
                        <a href="<?php echo e(route('sales.index')); ?>" class="text-sm text-primary-blue hover:underline"><?php echo e(__('View All')); ?></a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Receipt')); ?></th>
                                    <th><?php echo e(__('Time')); ?></th>
                                    <th><?php echo e(__('Amount')); ?></th>
                                    <th><?php echo e(__('Staff')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->recent_sales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sale): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <tr>
                                        <td class="font-medium text-slate-900"><?php echo e($sale->receipt_no); ?></td>
                                        <td class="text-sm text-slate-600"><?php echo e($sale->sold_at->format('H:i')); ?></td>
                                        <td class="font-semibold text-green-600">XAF <?php echo e(number_format($sale->grand_total, 0, ',', ' ')); ?></td>
                                        <td class="text-sm text-slate-600"><?php echo e($sale->user?->name ?? '-'); ?></td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-sm text-slate-500 py-4"><?php echo e(__('No recent sales')); ?></td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Cshop\resources\views/livewire/branch-dashboard.blade.php ENDPATH**/ ?>