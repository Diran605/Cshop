<div class="ui-page">
    <div class="ui-page-container">
        
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900"><?php echo e(__('Clearance Performance Report')); ?></h1>
                <p class="text-sm text-slate-500 mt-1"><?php echo e(__('Track clearance sales, recovery rates, and losses')); ?></p>
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
            </div>
        </div>

        
        <div class="ui-card mb-6">
            <div class="ui-card-body">
                <div class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1"><?php echo e(__('From')); ?></label>
                        <input type="date" wire:model.live="date_from" class="ui-input">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1"><?php echo e(__('To')); ?></label>
                        <input type="date" wire:model.live="date_to" class="ui-input">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1"><?php echo e(__('Action Type')); ?></label>
                        <select wire:model.live="filter_action" class="ui-input">
                            <option value="all"><?php echo e(__('All Actions')); ?></option>
                            <option value="sold"><?php echo e(__('Sold')); ?></option>
                            <option value="discount"><?php echo e(__('Discounted')); ?></option>
                            <option value="donate"><?php echo e(__('Donated')); ?></option>
                            <option value="dispose"><?php echo e(__('Disposed')); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <div class="ui-kpi-card">
                <div class="text-xs text-slate-500"><?php echo e(__('Items Processed')); ?></div>
                <div class="text-2xl font-bold text-slate-900"><?php echo e($this->stats['total_items']); ?></div>
            </div>

            <div class="ui-kpi-card">
                <div class="text-xs text-slate-500"><?php echo e(__('Original Value')); ?></div>
                <div class="text-xl font-bold text-slate-900">XAF <?php echo e(number_format($this->stats['total_original_value'], 0, ',', ' ')); ?></div>
            </div>

            <div class="ui-kpi-card bg-green-50 border-green-200">
                <div class="text-xs text-green-600"><?php echo e(__('Recovered Value')); ?></div>
                <div class="text-xl font-bold text-green-700">XAF <?php echo e(number_format($this->stats['total_recovered_value'], 0, ',', ' ')); ?></div>
            </div>

            <div class="ui-kpi-card bg-red-50 border-red-200">
                <div class="text-xs text-red-600"><?php echo e(__('Loss Value')); ?></div>
                <div class="text-xl font-bold text-red-700">XAF <?php echo e(number_format($this->stats['total_loss_value'], 0, ',', ' ')); ?></div>
            </div>

            <div class="ui-kpi-card">
                <div class="text-xs text-slate-500"><?php echo e(__('Recovery Rate')); ?></div>
                <div class="text-2xl font-bold <?php echo e($this->stats['recovery_rate'] >= 50 ? 'text-green-600' : 'text-amber-600'); ?>"><?php echo e($this->stats['recovery_rate']); ?>%</div>
            </div>
        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title"><?php echo e(__('Breakdown by Action')); ?></h3>
                    <div class="mt-4 space-y-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['sold' => __('Sold'), 'discount' => __('Discounted'), 'donate' => __('Donated'), 'dispose' => __('Disposed')]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                            <?php
                                $data = $this->stats['by_type']->get($type);
                            ?>
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg flex items-center justify-center <?php echo e($type === 'sold' ? 'bg-green-100 text-green-600' : ($type === 'discount' ? 'bg-blue-100 text-blue-600' : ($type === 'donate' ? 'bg-purple-100 text-purple-600' : 'bg-red-100 text-red-600'))); ?>">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'sold'): ?>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        <?php elseif($type === 'discount'): ?>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" /></svg>
                                        <?php elseif($type === 'donate'): ?>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" /></svg>
                                        <?php else: ?>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-900"><?php echo e($label); ?></div>
                                        <div class="text-xs text-slate-500"><?php echo e($data?->qty ?? 0); ?> <?php echo e(__('items')); ?></div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold <?php echo e($type === 'dispose' || $type === 'donate' ? 'text-red-600' : 'text-green-600'); ?>">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($type === 'dispose' || $type === 'donate'): ?>
                                            -XAF <?php echo e(number_format($data?->loss ?? 0, 0, ',', ' ')); ?>

                                        <?php else: ?>
                                            XAF <?php echo e(number_format($data?->recovered ?? 0, 0, ',', ' ')); ?>

                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    </div>
                </div>
            </div>

            
            <div class="ui-card">
                <div class="ui-card-body">
                    <h3 class="ui-card-title"><?php echo e(__('Daily Trend')); ?></h3>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->dailyTrend->count() > 0): ?>
                        <div class="mt-4">
                            <div class="flex items-end justify-between h-40 gap-1">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->dailyTrend; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <?php
                                        $max = $this->dailyTrend->max('recovered') ?: 1;
                                        $height = $max > 0 ? ($day->recovered / $max) * 100 : 0;
                                    ?>
                                    <div class="flex-1 flex flex-col items-center gap-1">
                                        <div class="text-[10px] text-slate-400">XAF <?php echo e(number_format($day->recovered, 0, ',', ' ')); ?></div>
                                        <div class="w-full bg-green-200 rounded-t relative" style="height: <?php echo e(max($height, 4)); ?>%">
                                            <div class="absolute inset-0 bg-green-500 rounded-t opacity-80"></div>
                                        </div>
                                        <div class="text-[10px] text-slate-400"><?php echo e(Carbon\Carbon::parse($day->date)->format('d')); ?></div>
                                    </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="mt-4 text-center py-8 text-slate-500"><?php echo e(__('No data for selected period')); ?></div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="ui-card">
            <div class="ui-card-body p-0">
                <div class="p-4 border-b border-slate-200">
                    <h3 class="ui-card-title"><?php echo e(__('Recent Clearance Actions')); ?></h3>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->recentActions->count() > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Date')); ?></th>
                                    <th><?php echo e(__('Product')); ?></th>
                                    <th><?php echo e(__('Action')); ?></th>
                                    <th class="text-right"><?php echo e(__('Qty')); ?></th>
                                    <th class="text-right"><?php echo e(__('Original')); ?></th>
                                    <th class="text-right"><?php echo e(__('Recovered')); ?></th>
                                    <th class="text-right"><?php echo e(__('Loss')); ?></th>
                                    <th><?php echo e(__('By')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->recentActions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <tr>
                                        <td><?php echo e($action->created_at->format('d M H:i')); ?></td>
                                        <td><?php echo e($action->clearanceItem?->product?->name ?? '-'); ?></td>
                                        <td>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium <?php echo e($action->action_type === 'sold' ? 'bg-green-100 text-green-800' : ($action->action_type === 'discount' ? 'bg-blue-100 text-blue-800' : ($action->action_type === 'donate' ? 'bg-purple-100 text-purple-800' : 'bg-red-100 text-red-800'))); ?>">
                                                <?php echo e(__($action->action_type === 'sold' ? 'Sold' : ($action->action_type === 'discount' ? 'Discounted' : ($action->action_type === 'donate' ? 'Donated' : 'Disposed')))); ?>

                                            </span>
                                        </td>
                                        <td class="text-right"><?php echo e($action->quantity); ?></td>
                                        <td class="text-right">XAF <?php echo e(number_format($action->original_value, 0, ',', ' ')); ?></td>
                                        <td class="text-right text-green-600">XAF <?php echo e(number_format($action->recovered_value, 0, ',', ' ')); ?></td>
                                        <td class="text-right text-red-600">XAF <?php echo e(number_format($action->loss_value, 0, ',', ' ')); ?></td>
                                        <td class="text-sm text-slate-500"><?php echo e($action->user?->name ?? '-'); ?></td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="p-12 text-center text-slate-500"><?php echo e(__('No actions recorded for this period')); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Cshop\resources\views/livewire/clearance/clearance-reports.blade.php ENDPATH**/ ?>