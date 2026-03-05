<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processComponentKey($component); ?>

      <?php $__env->slot('header', null, []); ?> 
        <div>
            <h2 class="ui-page-title">
                <?php echo e(__('Retail Dashboard')); ?>

            </h2>
            <div class="ui-page-subtitle">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user() && auth()->user()->branch): ?>
                    <?php echo e(__('Branch:')); ?>

                    <span class="font-medium"><?php echo e(auth()->user()->branch->name); ?></span>
                <?php elseif(auth()->user() && auth()->user()->role === 'super_admin'): ?>
                    <span class="font-medium"><?php echo e(__('Super Admin')); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

     <div class="ui-page">
        <div class="ui-page-container">
            
            <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="ui-kpi-card">
                    <div>
                        <div class="ui-kpi-title">
                            <?php echo e(__('Total Sales (This Month)')); ?>

                        </div>
                        <div class="ui-kpi-value">
                            XAF <?php echo e(number_format((float) $salesTotal, 0, ',', ' ')); ?>

                        </div>
                    </div>
                </div>

                <div class="ui-kpi-card">
                    <div>
                        <div class="ui-kpi-title">
                            <?php echo e(__('Inventory Value')); ?>

                        </div>
                        <div class="ui-kpi-value">
                            XAF <?php echo e(number_format((float) $inventoryValue, 0, ',', ' ')); ?>

                        </div>
                    </div>
                </div>

                <div class="ui-kpi-card bg-red-50 border-red-200">
                    <div>
                        <div class="ui-kpi-title text-red-600">
                            <?php echo e(__('Low Stock Items')); ?>

                        </div>
                        <div class="ui-kpi-value text-red-700">
                            <?php echo e($lowStockCount ?? 0); ?>

                        </div>
                    </div>
                </div>

                <div class="ui-kpi-card bg-amber-50 border-amber-200">
                    <div>
                        <div class="ui-kpi-title text-amber-600">
                            <?php echo e(__('Expiring Soon')); ?>

                        </div>
                        <div class="ui-kpi-value text-amber-700">
                            <?php echo e($expiringCount ?? 0); ?>

                        </div>
                    </div>
                </div>
            </div>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $isSuperAdmin): ?>
                <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['alerts.stock_adjustment', 'alerts.expired_stock', 'alerts.expiry_warning', 'alerts.low_stock'])): ?>
                    <div class="mb-6">
                        <?php
$__split = function ($name, $params = []) {
    return [$name, $params];
};
[$__name, $__params] = $__split('dashboard-alerts', []);

$key = null;
$__componentSlots = [];

$key ??= \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::generateKey('lw-2598281292-0', $key);

$__html = app('livewire')->mount($__name, $__params, $key, $__componentSlots);

echo $__html;

unset($__html);
unset($__name);
unset($__params);
unset($__componentSlots);
unset($__split);
if (isset($__slots)) unset($__slots);
?>
                    </div>
                <?php endif; ?>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $isSuperAdmin): ?>
                <div class="mb-6 ui-card">
                    <div class="ui-card-body">
                        <h3 class="ui-card-title"><?php echo e(__('Inventory Value by Category')); ?></h3>
                        <div class="mt-4 overflow-x-auto">
                            <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('Category')); ?></th>
                                        <th class="text-right"><?php echo e(__('Value')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $inventoryByCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <tr>
                                            <td><?php echo e($row->category_name); ?></td>
                                            <td class="text-right">XAF <?php echo e(number_format((float) $row->inventory_value, 0, ',', ' ')); ?></td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($inventoryByCategory) === 0): ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-sm text-slate-500"><?php echo e(__('No inventory data found.')); ?></td>
                                        </tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="mb-6 ui-card">
                    <div class="ui-card-body">
                        <h3 class="ui-card-title"><?php echo e(__('Top Branches by Sales (This Month)')); ?></h3>
                        <div class="mt-4 overflow-x-auto">
                            <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('Branch')); ?></th>
                                        <th class="text-right"><?php echo e(__('Sales')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $topBranchesBySales; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <tr>
                                            <td><?php echo e($row->branch_name); ?></td>
                                            <td class="text-right">XAF <?php echo e(number_format((float) $row->sales_total, 0, ',', ' ')); ?></td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($topBranchesBySales) === 0): ?>
                                        <tr>
                                            <td colspan="2" class="text-center text-sm text-slate-500"><?php echo e(__('No sales data found.')); ?></td>
                                        </tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="ui-card">
                    <a href="<?php echo e(route('products.index')); ?>" class="block p-6 hover:bg-slate-50/70">
                        <div class="text-sm text-slate-600">Setup</div>
                        <div class="mt-1 text-lg font-semibold text-slate-900">Products</div>
                        <div class="mt-2 text-sm text-slate-600">Manage product catalog, pricing, and bulk settings.</div>
                    </a>
                </div>
 
                 <div class="ui-card">
                     <a href="<?php echo e(route('setup.categories')); ?>" class="block p-6 hover:bg-slate-50/70">
                         <div class="text-sm text-slate-600">Setup</div>
                         <div class="mt-1 text-lg font-semibold text-slate-900">Categories</div>
                         <div class="mt-2 text-sm text-slate-600">Create and organize product categories.</div>
                     </a>
                 </div>
 
                 <div class="ui-card">
                     <a href="<?php echo e(route('setup.bulk_types')); ?>" class="block p-6 hover:bg-slate-50/70">
                         <div class="text-sm text-slate-600">Setup</div>
                         <div class="mt-1 text-lg font-semibold text-slate-900">Bulk Units & Types</div>
                         <div class="mt-2 text-sm text-slate-600">Define packaging units and reusable bulk configurations.</div>
                         <div class="mt-3 text-sm font-semibold text-primary-blue">
                             <?php echo e(__('Go to Bulk Units')); ?>

                         </div>
                     </a>
                 </div>
 
                 <div class="ui-card">
                     <a href="<?php echo e(route('stock_in.index')); ?>" class="block p-6 hover:bg-slate-50/70">
                         <div class="text-sm text-slate-600">Operations</div>
                         <div class="mt-1 text-lg font-semibold text-slate-900">Stock In</div>
                         <div class="mt-2 text-sm text-slate-600">Receive inventory and generate receipts.</div>
                     </a>
                 </div>
 
                 <div class="ui-card">
                     <a href="<?php echo e(route('sales.index')); ?>" class="block p-6 hover:bg-slate-50/70">
                         <div class="text-sm text-slate-600">Operations</div>
                         <div class="mt-1 text-lg font-semibold text-slate-900">Sales</div>
                         <div class="mt-2 text-sm text-slate-600">Process transactions with stock validation.</div>
                     </a>
                 </div>
 
                 <div class="ui-card">
                     <a href="<?php echo e(route('reports.index')); ?>" class="block p-6 hover:bg-slate-50/70">
                         <div class="text-sm text-slate-600">Analytics</div>
                         <div class="mt-1 text-lg font-semibold text-slate-900">Reports</div>
                         <div class="mt-2 text-sm text-slate-600">View sales, inventory, and movement reports.</div>
                     </a>
                 </div>
             </div>
         </div>
     </div>
  <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Cshop\resources\views/dashboard.blade.php ENDPATH**/ ?>