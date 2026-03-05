<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title"><?php echo e(__('Roles')); ?></h2>
            <div class="ui-page-subtitle"><?php echo e(__('Create roles and assign permissions per branch.')); ?></div>
        </div>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(session('status')): ?>
            <div class="ui-alert-success">
                <?php echo e(session('status')); ?>

            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <div class="space-y-6">
            
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title"><?php echo e($editing_role_id ? __('Edit Role') : __('Create Role')); ?></h3>

                    <div class="mt-4 grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="ui-label"><?php echo e(__('Branch')); ?></label>
                            <select wire:model.live="branch_id" class="mt-1 ui-select">
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

                        <div class="md:col-span-2">
                            <label class="ui-label"><?php echo e(__('Role Name')); ?></label>
                            <input type="text" wire:model.defer="name" class="mt-1 ui-input" placeholder="e.g. cashier" />
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-1 text-sm text-red-600"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="mt-6 p-4 bg-gradient-to-r from-slate-50 to-slate-100 rounded-lg border border-slate-200">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-slate-800"><?php echo e(__('All Permissions')); ?></div>
                                <div class="text-xs text-slate-500 mt-0.5">
                                    <?php echo e($permission_stats['selected']); ?> of <?php echo e($permission_stats['total']); ?> selected
                                    (<?php echo e($permission_stats['percentage']); ?>%)
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="w-32 h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-primary-blue rounded-full transition-all duration-300" style="width: <?php echo e($permission_stats['percentage']); ?>%"></div>
                                </div>
                                <button type="button" 
                                    wire:click="<?php echo e($permission_stats['percentage'] === 100 ? 'revokeAllPermissions' : 'grantAllPermissions'); ?>"
                                    class="px-3 py-1.5 text-xs font-medium rounded-md transition-colors <?php echo e($permission_stats['percentage'] === 100 ? 'bg-red-100 text-red-700 hover:bg-red-200' : 'bg-primary-blue text-white hover:bg-blue-600'); ?>">
                                    <?php echo e($permission_stats['percentage'] === 100 ? __('Clear All') : __('Select All')); ?>

                                </button>
                            </div>
                        </div>
                    </div>

                    
                    <div class="mt-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-2">
                            <label class="ui-label mb-0"><?php echo e(__('Permissions')); ?></label>
                            <div class="flex items-center gap-2">
                                <div class="relative">
                                    <input type="text" 
                                        wire:model.live.debounce.300ms="permission_search" 
                                        placeholder="<?php echo e(__('Search permissions...')); ?>" 
                                        class="ui-input pl-8 w-full sm:w-64" />
                                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($permission_stats['selected'] > 0): ?>
                                    <button type="button" 
                                        wire:click="revokeAllPermissions"
                                        class="px-3 py-1.5 text-xs font-medium rounded-md bg-red-100 text-red-700 hover:bg-red-200 transition-colors whitespace-nowrap">
                                        <?php echo e(__('Clear All')); ?>

                                    </button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </div>
                        
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($permission_search !== ''): ?>
                            <div class="mb-3 text-xs text-slate-500">
                                <?php echo e(__('Showing')); ?> <?php echo e(collect($this->filtered_permissions)->sum('total')); ?> <?php echo e(__('permissions matching')); ?> "<?php echo e($permission_search); ?>"
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        
                        <div class="space-y-2">
                            <?php
                                $groupIcons = [
                                    'branches' => '🏢',
                                    'users' => '👥',
                                    'rbac' => '🔐',
                                    'products' => '📦',
                                    'stock_in' => '📥',
                                    'sales' => '💰',
                                    'expenses' => '💸',
                                    'reports' => '📊',
                                    'audit' => '🔍',
                                ];
                            ?>
                            
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->filtered_permissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $groupKey => $group): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(count($group['permissions']) > 0): ?>
                                    <?php 
                                        $iconKey = explode('.', $groupKey)[0]; 
                                        $isExpanded = in_array($groupKey, $expanded_permission_groups) || $permission_search !== '';
                                    ?>
                                    <div class="border border-slate-200 rounded-lg overflow-hidden">
                                        
                                        <div class="flex items-center justify-between px-4 py-3 bg-slate-50 cursor-pointer hover:bg-slate-100 transition-colors"
                                            wire:click="togglePermissionGroup('<?php echo e($groupKey); ?>')">
                                            <div class="flex items-center gap-3">
                                                <span class="text-lg"><?php echo e($groupIcons[$iconKey] ?? '📋'); ?></span>
                                                <div>
                                                    <div class="text-sm font-medium text-slate-900"><?php echo e($group['label']); ?></div>
                                                    <div class="text-xs text-slate-500"><?php echo e($group['selected']); ?>/<?php echo e($group['total']); ?> selected</div>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="w-20 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                                    <div class="h-full bg-primary-blue rounded-full" style="width: <?php echo e($group['percentage']); ?>%"></div>
                                                </div>
                                                <button type="button" 
                                                    wire:click.stop="toggleAllForModule('<?php echo e($groupKey); ?>')"
                                                    class="px-2 py-1 text-xs font-medium rounded transition-colors <?php echo e($group['all_selected'] ? 'bg-primary-blue text-white' : 'bg-slate-200 text-slate-600 hover:bg-slate-300'); ?>">
                                                    <?php echo e($group['all_selected'] ? __('All') : __('All')); ?>

                                                </button>
                                                <svg class="w-4 h-4 text-slate-400 transition-transform <?php echo e($isExpanded ? 'rotate-180' : ''); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </div>

                                        
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($isExpanded): ?>
                                            <div class="p-4 bg-white border-t border-slate-200">
                                                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-2">
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $group['permissions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                                        <?php
                                                            $parts = explode('.', $perm->name);
                                                            $action = end($parts);
                                                            $isSelected = in_array($perm->name, $selected_permissions);
                                                        ?>
                                                        <label class="flex items-center gap-2 p-2 rounded-md hover:bg-slate-50 cursor-pointer transition-colors <?php echo e($isSelected ? 'bg-blue-50' : ''); ?>">
                                                            <input type="checkbox" 
                                                                value="<?php echo e($perm->name); ?>" 
                                                                wire:click="togglePermission('<?php echo e($perm->name); ?>')"
                                                                <?php echo e($isSelected ? 'checked' : ''); ?>

                                                                class="w-4 h-4 text-primary-blue border-slate-300 rounded focus:ring-primary-blue cursor-pointer" />
                                                            <span class="text-sm <?php echo e($isSelected ? 'text-primary-blue font-medium' : 'text-slate-700'); ?>">
                                                                <?php echo e(ucfirst($action)); ?>

                                                            </span>
                                                        </label>
                                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['selected_permissions'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <div class="mt-2 text-sm text-red-600"><?php echo e($message); ?></div> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($editing_role_id): ?>
                            <button type="button" wire:click="closeEditModal" class="ui-btn-secondary w-full sm:w-auto">
                                <?php echo e(__('Cancel')); ?>

                            </button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <button type="button" wire:click="save" class="ui-btn-primary w-full sm:w-auto">
                            <?php echo e($editing_role_id ? __('Update Role') : __('Create Role')); ?>

                        </button>
                    </div>
                </div>
            </div>

            
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title"><?php echo e(__('Existing Roles')); ?></h3>

                    <div class="mt-4 overflow-x-auto">
                        <div class="ui-table-wrap">
                            <table class="ui-table">
                                <thead>
                                    <tr>
                                        <th><?php echo e(__('Role Name')); ?></th>
                                        <th><?php echo e(__('Permissions')); ?></th>
                                        <th class="text-right"><?php echo e(__('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                        <tr <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::processElementKey('role-{{ $role->id }}', get_defined_vars()); ?>wire:key="role-<?php echo e($role->id); ?>">
                                            <td class="font-medium text-slate-900">
                                                <button type="button" 
                                                    wire:click="toggleRolePermissions(<?php echo e($role->id); ?>)" 
                                                    class="flex items-center gap-2 hover:text-primary-blue transition-colors">
                                                    <svg class="w-4 h-4 text-slate-400 transition-transform <?php echo e($expanded_role_id === $role->id ? 'rotate-90' : ''); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                                    </svg>
                                                    <?php echo e($role->name); ?>

                                                </button>
                                            </td>
                                            <td class="text-slate-700">
                                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expanded_role_id === $role->id): ?>
                                                    <div class="flex flex-wrap gap-1 max-w-lg">
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $role->permissions()->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $perm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                                            <span class="inline-block px-2 py-0.5 text-xs bg-primary-blue/10 text-primary-blue rounded"><?php echo e($perm->name); ?></span>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($role->permissions()->count() === 0): ?>
                                                            <span class="text-xs text-slate-400 italic"><?php echo e(__('No permissions')); ?></span>
                                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-xs text-slate-500">
                                                        <?php echo e($role->permissions()->count()); ?> <?php echo e(__('permissions')); ?>

                                                    </span>
                                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            </td>
                                            <td class="text-right">
                                                <div class="inline-flex items-center gap-2">
                                                    <button type="button" wire:click="openEditModal(<?php echo e($role->id); ?>)" class="ui-btn-link"><?php echo e(__('Edit')); ?></button>
                                                    <button type="button" wire:click="openDeleteModal(<?php echo e($role->id); ?>)" class="ui-btn-link-danger"><?php echo e(__('Delete')); ?></button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>

                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($roles->isEmpty()): ?>
                                        <tr>
                                            <td colspan="3" class="text-center text-sm text-slate-500 py-8"><?php echo e(__('No roles found for this branch.')); ?></td>
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

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($show_delete_modal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" data-modal-root>
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeDeleteModal" data-modal-overlay></div>
            <div class="relative w-full max-w-md ui-card">
                <div class="p-4 border-b border-slate-200">
                    <div class="text-sm text-slate-500"><?php echo e(__('Confirm Delete')); ?></div>
                    <div class="mt-1 font-semibold text-slate-900"><?php echo e(__('Delete Role')); ?></div>
                </div>

                <div class="p-4">
                    <div class="text-sm text-slate-700">
                        <?php echo e(__('Are you sure you want to delete the role')); ?> <span class="font-semibold text-slate-900"><?php echo e($pending_delete_name ?: '-'); ?></span>?
                        <p class="mt-2 text-xs text-slate-500"><?php echo e(__('This action cannot be undone.')); ?></p>
                    </div>

                    <div class="mt-4 flex flex-col sm:flex-row items-stretch sm:items-center justify-end gap-3">
                        <button type="button" wire:click="closeDeleteModal" class="ui-btn-secondary w-full sm:w-auto" data-modal-close><?php echo e(__('Cancel')); ?></button>
                        <button type="button" wire:click="confirmDelete" class="ui-btn-danger w-full sm:w-auto"><?php echo e(__('Delete')); ?></button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Cshop\resources\views/livewire/setup/roles-index.blade.php ENDPATH**/ ?>