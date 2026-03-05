<div class="ui-page">
    <div class="ui-page-container">
        
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900"><?php echo e(__('Clearance Discount Rules')); ?></h1>
                <p class="text-sm text-slate-500 mt-1"><?php echo e(__('Configure automatic discount percentages based on days to expiry')); ?></p>
            </div>
            <div class="flex items-center gap-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->isSuperAdmin): ?>
                    <div class="flex items-center gap-2">
                        <label class="text-sm text-slate-600"><?php echo e(__('Branch:')); ?></label>
                        <select wire:model.live="filter_branch_id" class="ui-input w-48">
                            <option value="0"><?php echo e(__('All Branches')); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->branches; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $branch): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <option value="<?php echo e($branch->id); ?>"><?php echo e($branch->name); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <a href="<?php echo e(route('clearance.index')); ?>" class="ui-btn-secondary">
                    <?php echo e(__('Back to Manager')); ?>

                </a>
                <button wire:click="openModal()" class="ui-btn-primary">
                    <?php echo e(__('Add Rule')); ?>

                </button>
            </div>
        </div>

        
        <div class="ui-card mb-6 bg-blue-50 border-blue-200">
            <div class="ui-card-body">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-900"><?php echo e(__('How Discount Rules Work')); ?></h4>
                        <p class="text-sm text-blue-700 mt-1">
                            <?php echo e(__('When products approach expiry, the system automatically suggests discounts based on these rules. Products within the days range will get the corresponding discount percentage.')); ?>

                        </p>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="ui-card">
            <div class="ui-card-body p-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->discountRules->count() > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Days to Expiry')); ?></th>
                                    <th><?php echo e(__('Status Label')); ?></th>
                                    <th class="text-right"><?php echo e(__('Discount %')); ?></th>
                                    <th><?php echo e(__('Scope')); ?></th>
                                    <th><?php echo e(__('Active')); ?></th>
                                    <th><?php echo e(__('Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->discountRules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <tr class="<?php echo e(! $rule->is_active ? 'bg-gray-50' : ''); ?>">
                                        <td>
                                            <span class="font-medium"><?php echo e($rule->days_to_expiry_min); ?></span>
                                            <span class="text-slate-400"> - </span>
                                            <span class="font-medium"><?php echo e($rule->days_to_expiry_max); ?></span>
                                            <span class="text-slate-500 text-sm"><?php echo e(__('days')); ?></span>
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                <?php echo e($rule->status_label === 'Critical' ? 'bg-red-100 text-red-800' :
                                                   ($rule->status_label === 'Urgent' ? 'bg-orange-100 text-orange-800' :
                                                   ($rule->status_label === 'Approaching' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'))); ?>">
                                                <?php echo e($rule->status_label); ?>

                                            </span>
                                        </td>
                                        <td class="text-right">
                                            <span class="text-lg font-bold text-green-600"><?php echo e($rule->discount_percentage); ?>%</span>
                                        </td>
                                        <td>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rule->branch_id): ?>
                                                <span class="text-xs text-slate-500"><?php echo e(__('This Branch')); ?></span>
                                            <?php else: ?>
                                                <span class="text-xs text-blue-600"><?php echo e(__('Global (All Branches)')); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td>
                                            <button wire:click="toggleActive(<?php echo e($rule->id); ?>)" class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors <?php echo e($rule->is_active ? 'bg-green-500' : 'bg-gray-300'); ?>">
                                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform <?php echo e($rule->is_active ? 'translate-x-6' : 'translate-x-1'); ?>"></span>
                                            </button>
                                        </td>
                                        <td>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($rule->branch_id === auth()->user()->branch_id): ?>
                                                <div class="flex gap-2">
                                                    <button wire:click="openModal(<?php echo e($rule->id); ?>)" class="ui-btn-link">
                                                        <?php echo e(__('Edit')); ?>

                                                    </button>
                                                    <button wire:click="delete(<?php echo e($rule->id); ?>)" class="ui-btn-link-danger" onclick="return confirm('<?php echo e(__('Are you sure?')); ?>')">
                                                        <?php echo e(__('Delete')); ?>

                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-xs text-slate-400"><?php echo e(__('Read only')); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <p class="text-lg font-medium text-slate-600"><?php echo e(__('No Discount Rules')); ?></p>
                        <p class="text-sm text-slate-500 mt-1"><?php echo e(__('Add rules to automatically calculate clearance discounts.')); ?></p>
                        <button wire:click="openModal()" class="ui-btn-primary mt-4"><?php echo e(__('Add First Rule')); ?></button>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($show_modal): ?>
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="$toggle('show_modal')">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <div class="p-6 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">
                        <?php echo e($editing_id ? __('Edit Discount Rule') : __('Add Discount Rule')); ?>

                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Min Days')); ?></label>
                            <input type="number" wire:model="days_to_expiry_min" min="0" max="365" class="ui-input">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['days_to_expiry_min'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Max Days')); ?></label>
                            <input type="number" wire:model="days_to_expiry_max" min="0" max="365" class="ui-input">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['days_to_expiry_max'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Status Label')); ?></label>
                        <input type="text" wire:model="status_label" class="ui-input" placeholder="<?php echo e(__('e.g., Critical, Urgent, Approaching')); ?>">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['status_label'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Discount Percentage')); ?></label>
                        <div class="flex items-center gap-2">
                            <input type="range" wire:model.live="discount_percentage" min="0" max="100" step="5" class="flex-1">
                            <span class="w-16 text-center font-bold text-green-600 text-lg"><?php echo e($discount_percentage); ?>%</span>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['discount_percentage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-xs text-red-600 mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="is_active" id="is_active" class="rounded border-slate-300">
                        <label for="is_active" class="text-sm text-slate-700"><?php echo e(__('Active')); ?></label>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-200 flex justify-end gap-3">
                    <button wire:click="$toggle('show_modal')" class="ui-btn-secondary"><?php echo e(__('Cancel')); ?></button>
                    <button wire:click="save" class="ui-btn-primary"><?php echo e(__('Save Rule')); ?></button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session()->has('success')): ?>
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Cshop\resources\views/livewire/clearance/clearance-discount-rules.blade.php ENDPATH**/ ?>