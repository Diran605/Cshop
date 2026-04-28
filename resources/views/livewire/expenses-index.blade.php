<div class="ui-page">
    <div class="ui-page-container">
        <div class="mb-6">
            <h2 class="ui-page-title">{{ __('Expenses') }}</h2>
            <div class="ui-page-subtitle">{{ __('Record and manage business expenses.') }}</div>
        </div>

        @if (session('status'))
            <div class="ui-alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="ui-alert-warning">
                {{ session('warning') }}
            </div>
        @endif

        @if (session('error'))
            <div class="ui-alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="space-y-6">
            @if ($mode === 'add')
                <div class="ui-card">
                    <div class="ui-card-body">
                        <h3 class="ui-card-title">{{ __('Add Expense') }}</h3>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="ui-label">{{ __('Branch') }}</label>
                                @if ($isSuperAdmin)
                                    <select wire:model.live="branch_id" class="mt-1 ui-select">
                                        <option value="0">{{ __('Select...') }}</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('branch_id') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                                @else
                                    <div class="mt-1 rounded-lg border border-slate-300/80 bg-white/60 px-3 py-2 text-sm text-slate-700">
                                        {{ $branches->firstWhere('id', $branch_id)?->name ?? '-' }}
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Date') }}</label>
                                <input type="date" wire:model.defer="expense_date" class="mt-1 ui-input" />
                                @error('expense_date') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Amount') }}</label>
                                <input type="number" min="0" step="0.01" wire:model.defer="amount" class="mt-1 ui-input" />
                                @error('amount') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Payment Method') }}</label>
                                <select wire:model.defer="payment_method" class="mt-1 ui-select">
                                    <option value="cash">{{ __('Cash') }}</option>
                                    <option value="card">{{ __('Card') }}</option>
                                    <option value="transfer">{{ __('Transfer') }}</option>
                                </select>
                                @error('payment_method') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Expense Type') }}</label>
                                <select wire:model.defer="expense_type" class="mt-1 ui-select">
                                    <option value="">{{ __('Select type...') }}</option>
                                    @foreach ($expenseTypes as $type)
                                        <option value="{{ $type->name }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                                @error('expense_type') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Description (optional)') }}</label>
                                <input type="text" wire:model.defer="description" class="mt-1 ui-input" />
                                @error('description') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Notes (optional)') }}</label>
                                <textarea wire:model.defer="notes" rows="3" class="mt-1 ui-input"></textarea>
                                @error('notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        @error('expense')
                            <div class="mt-4 ui-alert-danger">{{ $message }}</div>
                        @enderror

                        <div class="mt-4 flex items-center justify-end gap-3">
                            <button type="button" wire:click="$set('mode', 'manage')" class="ui-btn-secondary">
                                {{ __('Manage Expenses') }}
                            </button>
                            <button type="button" wire:click="save" class="ui-btn-primary">
                                {{ __('Post Expense') }}
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            @if ($mode === 'manage')
                <div class="ui-card">
                    <div class="ui-card-body">
                        <div class="flex items-center justify-between gap-4">
                            <h3 class="ui-card-title">{{ __('Manage Expenses') }}</h3>
                            <div class="text-sm text-slate-500">
                                {{ __('Selected:') }}
                                <span class="font-medium">{{ count($selected_expenses) }}</span>
                            </div>
                        </div>

                        <div class="mt-4 grid grid-cols-1 md:grid-cols-6 gap-4">
                            @if ($isSuperAdmin)
                                <div class="md:col-span-2">
                                    <label class="ui-label">{{ __('Branch') }}</label>
                                    <select wire:model.live="branch_id" class="mt-1 ui-select">
                                        <option value="0">{{ __('All branches') }}</option>
                                        @foreach ($branches as $branch)
                                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div>
                                <label class="ui-label">{{ __('From') }}</label>
                                <input type="date" wire:model.live="expense_date_from" class="mt-1 ui-input" />
                            </div>

                            <div>
                                <label class="ui-label">{{ __('To') }}</label>
                                <input type="date" wire:model.live="expense_date_to" class="mt-1 ui-input" />
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Status') }}</label>
                                <select wire:model.live="expense_status" class="mt-1 ui-select">
                                    <option value="active">{{ __('Active') }}</option>
                                    <option value="voided">{{ __('Voided') }}</option>
                                    <option value="all">{{ __('All') }}</option>
                                </select>
                            </div>

                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Search') }}</label>
                                <input type="text" wire:model.live.debounce.300ms="expense_search" placeholder="{{ __('No / Branch / User / Description') }}" class="mt-1 ui-input" />
                            </div>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <button type="button" wire:click="selectAllExpensesForDay('{{ $expense_date_from }}')" class="ui-btn-secondary">
                                    {{ __('Select All For Day') }}
                                </button>
                                @if (count($selected_expenses) > 0)
                                    <button type="button" wire:click="clearSelectedExpenses" class="ui-btn-secondary">
                                        {{ __('Clear Selection') }}
                                    </button>
                                @endif
                            </div>

                            <div class="flex items-center gap-3">
                                <button type="button" wire:click="$set('mode', 'add')" class="ui-btn-primary">
                                    {{ __('Add Expense') }}
                                </button>
                            </div>
                        </div>

                        @error('expense')
                            <div class="mt-4 ui-alert-danger">{{ $message }}</div>
                        @enderror

                        <div class="mt-4 overflow-x-auto">
                            <div class="ui-table-wrap">
                                <table class="ui-table">
                                    <thead>
                                        <tr>
                                            <th></th>
                                            <th>{{ __('No') }}</th>
                                            <th>{{ __('Date') }}</th>
                                            <th>{{ __('Branch') }}</th>
                                            <th>{{ __('Method') }}</th>
                                            <th>{{ __('Type') }}</th>
                                            <th class="text-right">{{ __('Amount') }}</th>
                                            <th>{{ __('Description') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($expenses as $expense)
                                            <tr wire:key="expense-{{ $expense->id }}">
                                                <td>
                                                    <input type="checkbox" value="{{ $expense->id }}" wire:model.live="selected_expenses" class="ui-checkbox" />
                                                </td>
                                                <td>
                                                    <div class="font-medium text-slate-900">{{ $expense->expense_no }}</div>
                                                    <div class="text-xs text-slate-500">{{ $expense->created_at?->format('Y-m-d H:i') }}</div>
                                                </td>
                                                <td>{{ $expense->expense_date?->format('Y-m-d') }}</td>
                                                <td>{{ $expense->branch?->name ?? '-' }}</td>
                                                <td>{{ strtoupper($expense->payment_method) }}</td>
                                                <td>{{ $expense->expense_type ?: '-' }}</td>
                                                <td class="text-right text-slate-900">XAF {{ number_format((float) $expense->amount, 2) }}</td>
                                                <td>{{ $expense->description ?: '-' }}</td>
                                                <td>
                                                    @if ($expense->voided_at)
                                                        <span class="ui-badge-warning">{{ __('Voided') }}</span>
                                                    @else
                                                        <span class="ui-badge-success">{{ __('Active') }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    <div class="inline-flex items-center gap-3">
                                                        <button type="button" wire:click="openExpenseModal({{ $expense->id }})" class="ui-btn-link">{{ __('View') }}</button>
                                                        @if (! $expense->voided_at)
                                                            <button type="button" wire:click="openEditModal({{ $expense->id }})" class="ui-btn-link">{{ __('Edit') }}</button>
                                                            <button type="button" wire:click="openVoidModal({{ $expense->id }})" class="ui-btn-link-danger">{{ __('Void') }}</button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach

                                        @if ($expenses->isEmpty())
                                            <tr>
                                                <td colspan="10" class="ui-table-empty">{{ __('No expenses found.') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if ($show_expense_modal && $selectedExpense)
            <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeExpenseModal" data-modal-overlay></div>
                <div class="relative w-full max-w-3xl mx-4 ui-card">
                    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Expense Details') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ $selectedExpense->expense_no }}</div>
                            <div class="mt-1 text-sm text-slate-600">
                                {{ $selectedExpense->branch?->name ?? '-' }}
                                @if ($selectedExpense->user)
                                    {{ '• ' . $selectedExpense->user->name }}
                                @endif
                                {{ '• ' . ($selectedExpense->expense_date?->format('Y-m-d') ?? '-') }}
                            </div>
                        </div>
                        <button type="button" wire:click="closeExpenseModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                    </div>

                    <div class="p-4 space-y-4">
                        @if ($selectedExpense->voided_at)
                            <div class="ui-alert-warning">
                                <div class="font-semibold">{{ __('Voided') }}</div>
                                <div class="mt-1">{{ __('Voided at:') }} {{ $selectedExpense->voided_at?->format('Y-m-d H:i') }}</div>
                                @if ($selectedExpense->void_reason)
                                    <div class="mt-1">{{ __('Reason:') }} {{ $selectedExpense->void_reason }}</div>
                                @endif
                            </div>
                        @endif

                        <div class="ui-muted-panel space-y-1">
                            <div class="flex items-center justify-between">
                                <div>{{ __('Amount') }}</div>
                                <div class="font-semibold text-slate-900">XAF {{ number_format((float) $selectedExpense->amount, 2) }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>{{ __('Method') }}</div>
                                <div class="font-medium">{{ strtoupper($selectedExpense->payment_method) }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>{{ __('Type') }}</div>
                                <div class="font-medium">{{ $selectedExpense->expense_type ?: '-' }}</div>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>{{ __('Description') }}</div>
                                <div class="font-medium">{{ $selectedExpense->description ?: '-' }}</div>
                            </div>
                        </div>

                        @if ($selectedExpense->notes)
                            <div class="text-sm text-slate-700">{{ $selectedExpense->notes }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if ($show_edit_modal)
            <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeEditModal" data-modal-overlay></div>
                <div class="relative w-full max-w-3xl mx-4 ui-card">
                    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Edit Expense') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ __('Full Edit') }}</div>
                        </div>
                        <button type="button" wire:click="closeEditModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                    </div>

                    <div class="p-4 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="ui-label">{{ __('Date') }}</label>
                                <input type="date" wire:model.defer="edit_expense_date" class="mt-1 ui-input" />
                                @error('edit_expense_date') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Amount') }}</label>
                                <input type="number" min="0" step="0.01" wire:model.defer="edit_amount" class="mt-1 ui-input" />
                                @error('edit_amount') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Payment Method') }}</label>
                                <select wire:model.defer="edit_payment_method" class="mt-1 ui-select">
                                    <option value="cash">{{ __('Cash') }}</option>
                                    <option value="card">{{ __('Card') }}</option>
                                    <option value="transfer">{{ __('Transfer') }}</option>
                                </select>
                                @error('edit_payment_method') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Expense Type (optional)') }}</label>
                                <input type="text" wire:model.defer="edit_expense_type" class="mt-1 ui-input" />
                                @error('edit_expense_type') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div>
                                <label class="ui-label">{{ __('Description (optional)') }}</label>
                                <input type="text" wire:model.defer="edit_description" class="mt-1 ui-input" />
                                @error('edit_description') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="ui-label">{{ __('Notes (optional)') }}</label>
                                <textarea wire:model.defer="edit_notes" rows="3" class="mt-1 ui-input"></textarea>
                                @error('edit_notes') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3">
                            <button type="button" wire:click="closeEditModal" class="ui-btn-secondary">{{ __('Cancel') }}</button>
                            <button type="button" wire:click="saveEdit" class="ui-btn-primary">{{ __('Save Changes') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($show_void_modal)
            <div class="fixed inset-0 z-50 flex items-center justify-center" data-modal-root>
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" wire:click="closeVoidModal" data-modal-overlay></div>
                <div class="relative w-full max-w-lg mx-4 ui-card">
                    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
                        <div>
                            <div class="text-sm text-slate-500">{{ __('Void Expense') }}</div>
                            <div class="mt-1 font-semibold text-slate-900">{{ __('Confirm Void') }}</div>
                        </div>
                        <button type="button" wire:click="closeVoidModal" class="ui-btn-secondary" data-modal-close>{{ __('Close') }}</button>
                    </div>
                    <div class="p-4 space-y-4">
                        <div class="text-sm text-slate-700">
                            {{ __('This will mark the expense as voided.') }}
                        </div>
                        <div>
                            <label class="ui-label">{{ __('Reason (optional)') }}</label>
                            <textarea wire:model.defer="void_reason" rows="2" class="mt-1 ui-input"></textarea>
                            @error('void_reason') <div class="mt-1 text-sm text-red-600">{{ $message }}</div> @enderror
                        </div>
                        <div class="flex items-center justify-end gap-3">
                            <button type="button" wire:click="closeVoidModal" class="ui-btn-secondary">{{ __('Cancel') }}</button>
                            <button type="button" wire:click="confirmVoidExpense" class="ui-btn-danger">{{ __('Void') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
