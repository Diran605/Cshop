<div class="ui-page">
    <div class="ui-page-container">
        
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-900"><?php echo e(__('Clearance Manager')); ?></h1>
                <p class="text-sm text-slate-500 mt-1"><?php echo e(__('Manage products approaching expiry with discounts, donations, or disposals')); ?></p>
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
                <a href="<?php echo e(route('clearance.rules')); ?>" class="ui-btn-secondary">
                    <?php echo e(__('Discount Rules')); ?>

                </a>
                <a href="<?php echo e(route('clearance.reports')); ?>" class="ui-btn-secondary">
                    <?php echo e(__('Reports')); ?>

                </a>
            </div>
        </div>

        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="ui-kpi-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-orange-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500"><?php echo e(__('Items Pending')); ?></div>
                        <div class="text-xl font-bold text-slate-900"><?php echo e($this->stats['total_pending']); ?></div>
                    </div>
                </div>
            </div>

            <div class="ui-kpi-card bg-red-50 border-red-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-red-600"><?php echo e(__('Value at Risk')); ?></div>
                        <div class="text-xl font-bold text-red-700">XAF <?php echo e(number_format($this->stats['total_value_at_risk'], 0, ',', ' ')); ?></div>
                    </div>
                </div>
            </div>

            <div class="ui-kpi-card">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500"><?php echo e(__('Urgent/Critical')); ?></div>
                        <div class="text-xl font-bold text-amber-600"><?php echo e($this->stats['by_status']['urgent'] + $this->stats['by_status']['critical']); ?></div>
                    </div>
                </div>
            </div>

            <div class="ui-kpi-card bg-gray-50 border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-gray-100 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                        </svg>
                    </div>
                    <div>
                        <div class="text-xs text-gray-600"><?php echo e(__('Expired')); ?></div>
                        <div class="text-xl font-bold text-gray-700"><?php echo e($this->stats['by_status']['expired']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="ui-card mb-6">
            <div class="ui-card-body">
                <div class="flex flex-wrap gap-4">
                    <div class="flex-1 min-w-[200px]">
                        <label class="block text-xs font-medium text-slate-600 mb-1"><?php echo e(__('Search Product')); ?></label>
                        <input type="text" wire:model.live.debounce.300ms="search" class="ui-input" placeholder="<?php echo e(__('Search by product name...')); ?>">
                    </div>
                    <div class="w-40">
                        <label class="block text-xs font-medium text-slate-600 mb-1"><?php echo e(__('Status')); ?></label>
                        <select wire:model.live="filter_status" class="ui-input">
                            <option value="all"><?php echo e(__('All Status')); ?></option>
                            <option value="approaching"><?php echo e(__('Approaching')); ?></option>
                            <option value="urgent"><?php echo e(__('Urgent')); ?></option>
                            <option value="critical"><?php echo e(__('Critical')); ?></option>
                            <option value="expired"><?php echo e(__('Expired')); ?></option>
                            <option value="actioned"><?php echo e(__('Actioned')); ?></option>
                        </select>
                    </div>
                    <div class="w-40">
                        <label class="block text-xs font-medium text-slate-600 mb-1"><?php echo e(__('Action')); ?></label>
                        <select wire:model.live="filter_action" class="ui-input">
                            <option value="pending"><?php echo e(__('Pending')); ?></option>
                            <option value="actioned"><?php echo e(__('Actioned')); ?></option>
                            <option value="all"><?php echo e(__('All')); ?></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="ui-card">
            <div class="ui-card-body p-0">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->clearanceItems->count() > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="ui-table">
                            <thead>
                                <tr>
                                    <th><?php echo e(__('Product')); ?></th>
                                    <th><?php echo e(__('Expiry')); ?></th>
                                    <th><?php echo e(__('Days Left')); ?></th>
                                    <th><?php echo e(__('Status')); ?></th>
                                    <th class="text-right"><?php echo e(__('Qty')); ?></th>
                                    <th class="text-right"><?php echo e(__('Original Price')); ?></th>
                                    <th class="text-right"><?php echo e(__('Suggested Discount')); ?></th>
                                    <th class="text-right"><?php echo e(__('Clearance Price')); ?></th>
                                    <th><?php echo e(__('Actions')); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->clearanceItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                    <tr class="<?php echo e($item->status === 'expired' ? 'bg-gray-50' : ''); ?>">
                                        <td>
                                            <div class="font-medium text-slate-900"><?php echo e($item->product?->name ?? '-'); ?></div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->action_type): ?>
                                                <span class="text-xs text-slate-500"><?php echo e(ucfirst($item->action_type)); ?> by <?php echo e($item->actionedBy?->name); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td><?php echo e($item->expiry_date->format('d M Y')); ?></td>
                                        <td>
                                            <?php $days = $item->days_to_expiry; ?>
                                            <span class="<?php echo e($days < 0 ? 'text-gray-500' : ($days <= 3 ? 'text-red-600 font-bold' : ($days <= 7 ? 'text-orange-600' : 'text-amber-600'))); ?>">
                                                <?php echo e($days < 0 ? __('Expired') : $days); ?> <?php echo e($days >= 0 ? __('days') : __('ago')); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($this->getStatusBadgeClass($item->status)); ?>">
                                                <?php echo e(ucfirst($item->status)); ?>

                                            </span>
                                        </td>
                                        <td class="text-right font-medium"><?php echo e($item->quantity); ?></td>
                                        <td class="text-right">XAF <?php echo e(number_format($item->original_price, 0, ',', ' ')); ?></td>
                                        <td class="text-right">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->suggested_discount_pct > 0): ?>
                                                <span class="text-green-600"><?php echo e($item->suggested_discount_pct); ?>%</span>
                                            <?php else: ?>
                                                <span class="text-slate-400">-</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->clearance_price): ?>
                                                <span class="text-green-600 font-medium">XAF <?php echo e(number_format($item->clearance_price, 0, ',', ' ')); ?></span>
                                            <?php else: ?>
                                                <span class="text-slate-400">-</span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($item->status !== 'actioned'): ?>
                                                <div class="flex items-center gap-2">
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clearance.discount')): ?>
                                                        <button wire:click="openDiscountModal(<?php echo e($item->id); ?>)" class="btn-xs bg-green-100 text-green-700 hover:bg-green-200" title="<?php echo e(__('Discount')); ?>">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                            </svg>
                                                            <?php echo e(__('Discount')); ?>

                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clearance.donate')): ?>
                                                        <button wire:click="openDonateModal(<?php echo e($item->id); ?>)" class="btn-xs bg-purple-100 text-purple-700 hover:bg-purple-200" title="<?php echo e(__('Donate')); ?>">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                                            </svg>
                                                            <?php echo e(__('Donate')); ?>

                                                        </button>
                                                    <?php endif; ?>
                                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('clearance.dispose')): ?>
                                                        <button wire:click="openDisposeModal(<?php echo e($item->id); ?>)" class="btn-xs bg-red-100 text-red-700 hover:bg-red-200" title="<?php echo e(__('Dispose')); ?>">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                            <?php echo e(__('Dispose')); ?>

                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-xs text-green-600"><?php echo e(__('Completed')); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </td>
                                    </tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-t border-slate-200">
                        <?php echo e($this->clearanceItems->links()); ?>

                    </div>
                <?php else: ?>
                    <div class="p-12 text-center">
                        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-lg font-medium text-slate-600"><?php echo e(__('No Clearance Items')); ?></p>
                        <p class="text-sm text-slate-500 mt-1"><?php echo e(__('All products are within safe expiry dates.')); ?></p>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>
    </div>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($show_discount_modal): ?>
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="$toggle('show_discount_modal')">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4">
                <div class="p-6 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900"><?php echo e(__('Apply Clearance Discount')); ?></h3>
                </div>
                <div class="p-6 space-y-4">
                    <div class="bg-slate-50 rounded-lg p-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-600"><?php echo e(__('Original Price')); ?></span>
                            <span class="font-semibold text-slate-900">XAF <?php echo e(number_format($discount_original_price, 0, ',', ' ')); ?></span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Discount Percentage')); ?></label>
                        <div class="flex items-center gap-2">
                            <input type="range" wire:model.live="discount_percentage" min="0" max="100" step="5" class="flex-1">
                            <span class="w-16 text-center font-bold text-green-600"><?php echo e($discount_percentage); ?>%</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Clearance Price')); ?></label>
                        <div class="flex items-center gap-2">
                            <span class="text-slate-500">XAF</span>
                            <input type="number" wire:model="discount_custom_price" class="ui-input flex-1" step="0.01">
                        </div>
                        <p class="text-xs text-slate-500 mt-1"><?php echo e(__('Suggested: XAF')); ?> <?php echo e(number_format($discount_suggested_price, 0, ',', ' ')); ?></p>
                    </div>

                    <div class="bg-green-50 rounded-lg p-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-green-600"><?php echo e(__('Customer Savings')); ?></span>
                            <span class="font-semibold text-green-700">XAF <?php echo e(number_format($discount_original_price - $discount_custom_price, 0, ',', ' ')); ?></span>
                        </div>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-200 flex justify-end gap-3">
                    <button wire:click="$toggle('show_discount_modal')" class="ui-btn-secondary"><?php echo e(__('Cancel')); ?></button>
                    <button wire:click="applyDiscount" class="ui-btn-primary"><?php echo e(__('Apply Discount')); ?></button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($show_donate_modal): ?>
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="$toggle('show_donate_modal')">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
                <div class="p-6 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900"><?php echo e(__('Record Donation')); ?></h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Quantity to Donate')); ?></label>
                        <input type="number" wire:model="donate_quantity" min="1" max="<?php echo e($donate_max_quantity); ?>" class="ui-input">
                        <p class="text-xs text-slate-500 mt-1"><?php echo e(__('Available')); ?>: <?php echo e($donate_max_quantity); ?></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Organization Name')); ?> *</label>
                        <input type="text" wire:model="donate_organization" class="ui-input" placeholder="<?php echo e(__('e.g., Local Food Bank')); ?>">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Contact Person/Phone')); ?></label>
                        <input type="text" wire:model="donate_contact" class="ui-input">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Address')); ?></label>
                        <textarea wire:model="donate_address" class="ui-input" rows="2"></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Notes')); ?></label>
                        <textarea wire:model="donate_notes" class="ui-input" rows="2"></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-200 flex justify-end gap-3">
                    <button wire:click="$toggle('show_donate_modal')" class="ui-btn-secondary"><?php echo e(__('Cancel')); ?></button>
                    <button wire:click="recordDonation" class="ui-btn-primary bg-purple-600 hover:bg-purple-700"><?php echo e(__('Record Donation')); ?></button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($show_dispose_modal): ?>
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" wire:click.self="$toggle('show_dispose_modal')">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4">
                <div class="p-6 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900"><?php echo e(__('Record Disposal')); ?></h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Quantity to Dispose')); ?></label>
                        <input type="number" wire:model="dispose_quantity" min="1" max="<?php echo e($dispose_max_quantity); ?>" class="ui-input">
                        <p class="text-xs text-slate-500 mt-1"><?php echo e(__('Available')); ?>: <?php echo e($dispose_max_quantity); ?></p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Disposal Reason')); ?> *</label>
                        <select wire:model="dispose_reason" class="ui-input">
                            <option value=""><?php echo e(__('Select reason...')); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\Disposal::getReasons(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Disposal Method')); ?></label>
                        <select wire:model="dispose_method" class="ui-input">
                            <option value=""><?php echo e(__('Select method...')); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = \App\Models\Disposal::getMethods(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $value => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoop($loop->index); ?><?php endif; ?>
                                <option value="<?php echo e($value); ?>"><?php echo e($label); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2"><?php echo e(__('Notes')); ?></label>
                        <textarea wire:model="dispose_notes" class="ui-input" rows="2"></textarea>
                    </div>
                </div>
                <div class="p-6 border-t border-slate-200 flex justify-end gap-3">
                    <button wire:click="$toggle('show_dispose_modal')" class="ui-btn-secondary"><?php echo e(__('Cancel')); ?></button>
                    <button wire:click="recordDisposal" class="ui-btn-primary bg-red-600 hover:bg-red-700"><?php echo e(__('Record Disposal')); ?></button>
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
<?php /**PATH C:\xampp\htdocs\Cshop\resources\views/livewire/clearance/clearance-manager.blade.php ENDPATH**/ ?>