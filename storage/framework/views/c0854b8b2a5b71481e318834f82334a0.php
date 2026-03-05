<div>
<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title"><?php echo e(__('Activity Logs')); ?></h2>
            <div class="ui-page-subtitle"><?php echo e(__('Audit trail of system activities.')); ?></div>
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
                        <label class="ui-label"><?php echo e(__('User')); ?></label>
                        <select wire:model.live="user_id" class="mt-1 ui-select">
                            <option value="0"><?php echo e(__('All')); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="ui-label"><?php echo e(__('Action')); ?></label>
                        <input type="text" wire:model.debounce.300ms="action" class="mt-1 ui-input" placeholder="e.g. user.created" />
                    </div>

                    <div>
                        <label class="ui-label"><?php echo e(__('Search')); ?></label>
                        <input type="text" wire:model.debounce.300ms="search" placeholder="<?php echo e(__('Description / Subject')); ?>" class="mt-1 ui-input" />
                    </div>
                </div>

                <div class="mt-6 overflow-x-auto">
                    <div class="ui-table-wrap">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Date')); ?></th>
                                    <th><?php echo e(__('Branch')); ?></th>
                                    <th><?php echo e(__('Action')); ?></th>
                                    <th><?php echo e(__('Subject')); ?></th>
                                    <th><?php echo e(__('Description')); ?></th>
                                    <th><?php echo e(__('User')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $logs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('act-{{ $log->id }}', get_defined_vars()); ?>wire:key="act-<?php echo e($log->id); ?>">
                                        <td><?php echo e(optional($log->created_at)->format('Y-m-d H:i')); ?></td>
                                        <td><?php echo e($log->branch?->name ?? '-'); ?></td>
                                        <td class="font-medium text-slate-900"><?php echo e($log->action); ?></td>
                                        <td class="text-slate-700">
                                            <?php
                                                $st = $log->subject_type ? class_basename($log->subject_type) : null;
                                                $sid = $log->subject_id ? ('#' . $log->subject_id) : null;
                                            ?>
                                            <?php echo e($st ? ($st . ' ' . ($sid ?? '')) : '-'); ?>

                                        </td>
                                        <td><?php echo e($log->description ?? '-'); ?></td>
                                        <td><?php echo e($log->user?->name ?? '-'); ?></td>
                                        <td>
                                            <button wire:click="openDetailModal(<?php echo e($log->id); ?>)" class="ui-btn-link">
                                                <?php echo e(__('View')); ?>

                                            </button>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logs->isEmpty()): ?>
                                    <tr>
                                        <td colspan="7" class="ui-table-empty"><?php echo e(__('No activity found.')); ?></td>
                                    </tr>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($logs->hasPages()): ?>
                        <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="text-sm text-slate-600">
                                <?php echo e(__('Showing')); ?> <?php echo e($logs->firstItem()); ?> <?php echo e(__('to')); ?> <?php echo e($logs->lastItem()); ?> <?php echo e(__('of')); ?> <?php echo e($logs->total()); ?> <?php echo e(__('results')); ?>

                            </div>
                            <?php echo e($logs->links('pagination::tailwind')); ?>

                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Audit Detail Modal -->
<div x-data="{ show: <?php if ((object) ('show_detail_modal') instanceof \Livewire\WireDirective) : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('show_detail_modal'->value()); ?>')<?php echo e('show_detail_modal'->hasModifier('live') ? '.live' : ''); ?><?php else : ?>window.Livewire.find('<?php echo e($__livewire->getId()); ?>').entangle('<?php echo e('show_detail_modal'); ?>')<?php endif; ?> }" x-show="show" x-cloak style="display: none;">
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex min-h-screen items-center justify-center p-4 text-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-semibold leading-6 text-slate-900" id="modal-title">
                                <?php echo e(__('Activity Details')); ?>

                            </h3>
                            <div class="mt-4 space-y-4">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selected_log): ?>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="text-sm font-medium text-slate-500"><?php echo e(__('Date & Time')); ?></label>
                                            <div class="mt-1 text-sm text-slate-900">
                                                <?php echo e(optional($selected_log->created_at)->format('Y-m-d H:i:s')); ?>

                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-slate-500"><?php echo e(__('Branch')); ?></label>
                                            <div class="mt-1 text-sm text-slate-900">
                                                <?php echo e($selected_log->branch?->name ?? '-'); ?>

                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-slate-500"><?php echo e(__('User')); ?></label>
                                            <div class="mt-1 text-sm text-slate-900">
                                                <?php echo e($selected_log->user?->name ?? '-'); ?>

                                            </div>
                                        </div>
                                        <div>
                                            <label class="text-sm font-medium text-slate-500"><?php echo e(__('IP Address')); ?></label>
                                            <div class="mt-1 text-sm text-slate-900">
                                                <?php echo e($selected_log->ip_address ?? '-'); ?>

                                            </div>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-slate-500"><?php echo e(__('Action')); ?></label>
                                        <div class="mt-1 p-2 bg-slate-50 rounded text-sm text-slate-900 font-mono">
                                            <?php echo e($selected_log->action); ?>

                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-slate-500"><?php echo e(__('Subject')); ?></label>
                                        <div class="mt-1 text-sm text-slate-900">
                                            <?php
                                                $st = $selected_log->subject_type ? class_basename($selected_log->subject_type) : null;
                                                $sid = $selected_log->subject_id ? ('#' . $selected_log->subject_id) : null;
                                            ?>
                                            <?php echo e($st ? ($st . ' ' . ($sid ?? '')) : '-'); ?>

                                        </div>
                                    </div>

                                    <div>
                                        <label class="text-sm font-medium text-slate-500"><?php echo e(__('Description')); ?></label>
                                        <div class="mt-1 text-sm text-slate-900">
                                            <?php echo e($selected_log->description ?? '-'); ?>

                                        </div>
                                    </div>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($selected_log->meta && is_array($selected_log->meta) && count($selected_log->meta) > 0): ?>
                                        <div>
                                            <label class="text-sm font-medium text-slate-500"><?php echo e(__('Additional Details')); ?></label>
                                            <div class="mt-1 p-3 bg-slate-50 rounded text-sm text-slate-900">
                                                <pre class="whitespace-pre-wrap font-mono text-xs"><?php echo e(json_encode($selected_log->meta, JSON_PRETTY_PRINT)); ?></pre>
                                            </div>
                                        </div>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
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
</div>
<?php /**PATH C:\xampp\htdocs\Cshop\resources\views/livewire/activity-logs-index.blade.php ENDPATH**/ ?>