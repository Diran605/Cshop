<div>
    <div class="ui-page">
        <div class="ui-page-container">
            <div class="mb-6">
                <h2 class="ui-page-title"><?php echo e(__('Stock Movements')); ?></h2>
                <div class="ui-page-subtitle"><?php echo e(__('Audit trail of inventory movements.')); ?></div>
            </div>

            <div class="ui-card">
                <div class="ui-card-body">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-4">
                        <div>
                            <label class="ui-label"><?php echo e(__('From')); ?></label>
                            <input type="date" wire:model.live="date_from" class="mt-1 ui-input" />
                        </div>

                        <div>
                            <label class="ui-label"><?php echo e(__('To')); ?></label>
                            <input type="date" wire:model.live="date_to" class="mt-1 ui-input" />
                        </div>

                        <div>
                            <label class="ui-label"><?php echo e(__('Branch')); ?></label>
                            <select wire:model.live="branch_id" class="mt-1 ui-select" <?php if(! $isSuperAdmin): ?> disabled <?php endif; ?>>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSuperAdmin): ?>
                                    <option value="0"><?php echo e(__('All')); ?></option>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>

                        <div>
                            <label class="ui-label"><?php echo e(__('Product')); ?></label>
                            <select wire:model.live="product_id" class="mt-1 ui-select">
                                <option value="0"><?php echo e(__('All')); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <option value="<?php echo e($product->id); ?>"><?php echo e($product->name); ?></option>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </select>
                        </div>

                        <div>
                            <label class="ui-label"><?php echo e(__('Type')); ?></label>
                            <select wire:model.live="movement_type" class="mt-1 ui-select">
                                <option value="all"><?php echo e(__('All')); ?></option>
                                <option value="IN"><?php echo e(__('IN')); ?></option>
                                <option value="OUT"><?php echo e(__('OUT')); ?></option>
                            </select>
                        </div>

                        <div>
                            <label class="ui-label"><?php echo e(__('Search')); ?></label>
                            <input type="text" wire:model.debounce.300ms="search" placeholder="<?php echo e(__('Product/User')); ?>" class="mt-1 ui-input" />
                        </div>
                    </div>

                    <div class="mt-6 overflow-x-auto">
                        <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('Date')); ?></th>
                                        <th><?php echo e(__('Branch')); ?></th>
                                        <th><?php echo e(__('Product')); ?></th>
                                        <th><?php echo e(__('Type')); ?></th>
                                        <th class="text-right"><?php echo e(__('Qty')); ?></th>
                                        <th class="text-right"><?php echo e(__('Before')); ?></th>
                                        <th class="text-right"><?php echo e(__('After')); ?></th>
                                        <th class="text-right"><?php echo e(__('Unit Cost')); ?></th>
                                        <th class="text-right"><?php echo e(__('Unit Price')); ?></th>
                                        <th><?php echo e(__('User')); ?></th>
                                        <th><?php echo e(__('Ref')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $movements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('mv-{{ $m->id }}', get_defined_vars()); ?>wire:key="mv-<?php echo e($m->id); ?>">
                                            <td><?php echo e(optional($m->moved_at)->format('Y-m-d H:i')); ?></td>
                                            <td><?php echo e($m->branch?->name ?? '-'); ?></td>
                                            <td class="text-slate-900"><?php echo e($m->product?->name ?? '-'); ?></td>
                                            <td class="font-medium <?php echo e($m->movement_type === 'IN' ? 'text-green-700' : 'text-red-700'); ?>"><?php echo e($m->movement_type); ?></td>
                                            <td class="text-right text-slate-900"><?php echo e((int) $m->quantity); ?></td>
                                            <td class="text-right"><?php echo e((int) $m->before_stock); ?></td>
                                            <td class="text-right"><?php echo e((int) $m->after_stock); ?></td>
                                            <td class="text-right"><?php echo e($m->unit_cost !== null ? number_format((float) $m->unit_cost, 2) : '-'); ?></td>
                                            <td class="text-right"><?php echo e($m->unit_price !== null ? number_format((float) $m->unit_price, 2) : '-'); ?></td>
                                            <td><?php echo e($m->user?->name ?? '-'); ?></td>
                                            <td>
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($m->stock_in_receipt_id): ?>
                                                    <?php echo e(__('SI')); ?> #<?php echo e($m->stock_in_receipt_id); ?>

                                                <?php elseif($m->sales_receipt_id): ?>
                                                    <?php echo e(__('SL')); ?> #<?php echo e($m->sales_receipt_id); ?>

                                                <?php else: ?>
                                                    -
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td>
                                                <button wire:click="openDetailModal(<?php echo e($m->id); ?>)" class="ui-btn-link">
                                                    <?php echo e(__('View')); ?>

                                                </button>
                                            </td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($movements->isEmpty()): ?>
                                        <tr>
                                            <td colspan="12" class="ui-table-empty"><?php echo e(__('No movements found.')); ?></td>
                                        </tr>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </tbody>
                            </table>
                            </div>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(method_exists($movements, 'hasPages') && $movements->hasPages()): ?>
                                <div class="mt-4 flex items-center justify-between">
                                    <div class="text-sm text-slate-600">
                                        <?php echo e(__('Showing')); ?> <?php echo e($movements->firstItem()); ?> <?php echo e(__('to')); ?> <?php echo e($movements->lastItem()); ?> <?php echo e(__('of')); ?> <?php echo e($movements->total()); ?> <?php echo e(__('results')); ?>

                                    </div>
                                    <?php echo e($movements->links('pagination::tailwind')); ?>

                                </div>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($show_detail_modal): ?>
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg font-semibold leading-6 text-slate-900" id="modal-title">
                                    <?php echo e(__('Movement Details')); ?>

                                </h3>
                                <div class="mt-4 space-y-4">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selected_movement): ?>
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <label class="text-sm font-medium text-slate-500"><?php echo e(__('Date & Time')); ?></label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    <?php echo e(optional($selected_movement->moved_at)->format('Y-m-d H:i:s')); ?>

                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500"><?php echo e(__('Branch')); ?></label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    <?php echo e($selected_movement->branch?->name ?? '-'); ?>

                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500"><?php echo e(__('Product')); ?></label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    <?php echo e($selected_movement->product?->name ?? '-'); ?>

                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500"><?php echo e(__('Movement Type')); ?></label>
                                                <div class="mt-1 text-sm font-semibold <?php echo e($selected_movement->movement_type === 'IN' ? 'text-green-700' : 'text-red-700'); ?>">
                                                    <?php echo e($selected_movement->movement_type); ?>

                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500"><?php echo e(__('Quantity')); ?></label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    <?php echo e((int) $selected_movement->quantity); ?>

                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500"><?php echo e(__('User')); ?></label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    <?php echo e($selected_movement->user?->name ?? '-'); ?>

                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500"><?php echo e(__('Before Stock')); ?></label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    <?php echo e((int) $selected_movement->before_stock); ?>

                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500"><?php echo e(__('After Stock')); ?></label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    <?php echo e((int) $selected_movement->after_stock); ?>

                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500"><?php echo e(__('Unit Cost')); ?></label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    <?php echo e($selected_movement->unit_cost !== null ? number_format((float) $selected_movement->unit_cost, 2) : '-'); ?>

                                                </div>
                                            </div>
                                            <div>
                                                <label class="text-sm font-medium text-slate-500"><?php echo e(__('Unit Price')); ?></label>
                                                <div class="mt-1 text-sm text-slate-900">
                                                    <?php echo e($selected_movement->unit_price !== null ? number_format((float) $selected_movement->unit_price, 2) : '-'); ?>

                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="text-sm font-medium text-slate-500"><?php echo e(__('Reference')); ?></label>
                                            <div class="mt-1 text-sm text-slate-900">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selected_movement->stock_in_receipt_id): ?>
                                                    <?php echo e(__('Stock In Receipt')); ?> #<?php echo e($selected_movement->stock_in_receipt_id); ?>

                                                <?php elseif($selected_movement->sales_receipt_id): ?>
                                                    <?php echo e(__('Sales Receipt')); ?> #<?php echo e($selected_movement->sales_receipt_id); ?>

                                                <?php else: ?>
                                                    -
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" wire:click="closeDetailModal" class="ui-btn-primary">
                            <?php echo e(__('Close')); ?>

                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
</div>
<?php /**PATH C:\xampp\htdocs\Cshop\resources\views/livewire/stock-movements-index.blade.php ENDPATH**/ ?>