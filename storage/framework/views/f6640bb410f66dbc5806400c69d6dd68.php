<div class="ui-page">
    <div class="ui-page-container">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('status')): ?>
            <div class="mb-4 ui-alert ui-alert-success">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="mb-6">
            <h2 class="ui-page-title"><?php echo e(__('Unit Types')); ?></h2>
            <div class="ui-page-subtitle"><?php echo e(__('Manage unit types for products (bottles, packets, strips, etc.)')); ?></div>
        </div>

        <div class="ui-card">
            <div class="ui-card-body">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-4">
                    <div class="w-full sm:flex-1 sm:max-w-md">
                        <input type="text" wire:model.live.debounce.300ms="search" placeholder="<?php echo e(__('Search unit types...')); ?>" class="ui-input" />
                    </div>
                    <button type="button" wire:click="openModal" class="ui-btn-primary w-full sm:w-auto">
                        <?php echo e(__('Add Unit Type')); ?>

                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="ui-table">
                        <thead>
                            <tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSuperAdmin): ?>
                                    <th><?php echo e(__('Branch')); ?></th>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <th><?php echo e(__('Name')); ?></th>
                                <th><?php echo e(__('Status')); ?></th>
                                <th><?php echo e(__('Actions')); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $unitTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $unitType): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('ut-{{ $unitType->id }}', get_defined_vars()); ?>wire:key="ut-<?php echo e($unitType->id); ?>">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSuperAdmin): ?>
                                        <td><?php echo e($unitType->branch?->name ?? '-'); ?></td>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    <td><?php echo e($unitType->name); ?></td>
                                    <td>
                                        <button type="button" wire:click="toggleActive(<?php echo e($unitType->id); ?>)" class="ui-badge <?php echo e($unitType->is_active ? 'ui-badge-success' : 'ui-badge-secondary'); ?>">
                                            <?php echo e($unitType->is_active ? __('Active') : __('Inactive')); ?>

                                        </button>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            <button type="button" wire:click="edit(<?php echo e($unitType->id); ?>)" class="ui-btn-link"><?php echo e(__('Edit')); ?></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unitTypes->isEmpty()): ?>
                                <tr>
                                    <td colspan="<?php echo e($isSuperAdmin ? 4 : 3); ?>" class="ui-table-empty"><?php echo e(__('No unit types found.')); ?></td>
                                </tr>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($unitTypes->hasPages()): ?>
                    <div class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-slate-600">
                            <?php echo e(__('Showing')); ?> <?php echo e($unitTypes->firstItem()); ?> <?php echo e(__('to')); ?> <?php echo e($unitTypes->lastItem()); ?> <?php echo e(__('of')); ?> <?php echo e($unitTypes->total()); ?> <?php echo e(__('results')); ?>

                        </div>
                        <?php echo e($unitTypes->links('pagination::tailwind')); ?>

                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($show_modal): ?>
        <div class="fixed inset-0 z-50 flex items-start justify-center p-4 pt-8 sm:pt-12 overflow-y-auto" data-modal-root>
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeModal" data-modal-overlay></div>
            <div class="relative w-full max-w-lg ui-card flex flex-col mb-4 z-10">
                <div class="p-4 border-b border-slate-200 flex items-center justify-between shrink-0">
                    <div>
                        <div class="text-sm text-slate-500"><?php echo e($editingId ? __('Edit Unit Type') : __('Add Unit Type')); ?></div>
                        <div class="mt-1 font-semibold text-slate-900"><?php echo e($name ?: '-'); ?></div>
                    </div>
                    <button type="button" wire:click="closeModal" class="ui-btn-secondary" data-modal-close><?php echo e(__('Close')); ?></button>
                </div>

                <div class="p-4 overflow-y-auto flex-1 min-h-0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isSuperAdmin): ?>
                            <div class="sm:col-span-2">
                                <label class="ui-label"><?php echo e(__('Branch')); ?></label>
                                <select wire:model.live="branch_id" class="mt-1 ui-select">
                                    <option value="0"><?php echo e(__('Select...')); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['branch_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-1 text-sm text-red-600"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                        <div class="sm:col-span-2">
                            <label class="ui-label"><?php echo e(__('Name')); ?></label>
                            <input type="text" wire:model.defer="name" placeholder="<?php echo e(__('e.g., Bottle, Packet, Strip, Can')); ?>" class="mt-1 ui-input" />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-1 text-sm text-red-600"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>

                        <div class="sm:col-span-2">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model="is_active" class="rounded border-slate-300" />
                                <span class="text-sm text-slate-700"><?php echo e(__('Active')); ?></span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3 shrink-0">
                    <button type="button" wire:click="closeModal" class="ui-btn-secondary" data-modal-close>
                        <?php echo e(__('Cancel')); ?>

                    </button>
                    <button type="button" wire:click="save" class="ui-btn-primary">
                        <?php echo e(__('Save')); ?>

                    </button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Cshop\resources\views/livewire/settings/unit-types-index.blade.php ENDPATH**/ ?>