<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Expense;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class ExpensesIndex extends Component
{
    public string $mode = 'add';

    public int $branch_id = 0;

    public string $expense_date;
    public ?string $amount = null;
    public string $payment_method = 'cash';
    public ?string $expense_type = null;
    public ?string $description = null;
    public ?string $notes = null;

    public bool $isSuperAdmin = false;
    public int $auth_user_id = 0;

    public string $expense_search = '';
    public string $expense_date_from;
    public string $expense_date_to;
    public string $expense_status = 'active';

    /**
     * @var array<int>
     */
    public array $selected_expenses = [];

    public int $selected_expense_id = 0;
    public bool $show_expense_modal = false;

    public bool $show_edit_modal = false;
    public bool $show_void_modal = false;

    public int $editing_expense_id = 0;
    public int $edit_branch_id = 0;

    public string $edit_expense_date;
    public ?string $edit_amount = null;
    public string $edit_payment_method = 'cash';
    public ?string $edit_expense_type = null;
    public ?string $edit_description = null;
    public ?string $edit_notes = null;

    public int $pending_void_expense_id = 0;
    public ?string $void_reason = null;

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'min:1'],
            'expense_date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'in:cash,card,transfer'],
            'expense_type' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function mount(string $mode = 'add'): void
    {
        $mode = strtolower(trim($mode));
        $this->mode = in_array($mode, ['add', 'manage'], true) ? $mode : 'add';

        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->auth_user_id = (int) ($user?->id ?? 0);

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        } else {
            $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        }

        $today = Carbon::today();
        $this->expense_date = $today->toDateString();
        $this->expense_date_from = $today->toDateString();
        $this->expense_date_to = $today->toDateString();

        $this->payment_method = 'cash';
        $this->amount = null;
        $this->expense_type = null;
        $this->description = null;
        $this->notes = null;

        $this->expense_search = '';
        $this->expense_status = 'active';
        $this->selected_expenses = [];

        $this->show_expense_modal = false;
        $this->selected_expense_id = 0;

        $this->show_edit_modal = false;
        $this->show_void_modal = false;
        $this->editing_expense_id = 0;
        $this->edit_branch_id = 0;
        $this->edit_expense_date = $today->toDateString();
        $this->edit_payment_method = 'cash';
        $this->edit_amount = null;
        $this->edit_expense_type = null;
        $this->edit_description = null;
        $this->edit_notes = null;

        $this->pending_void_expense_id = 0;
        $this->void_reason = null;
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;
            $this->selected_expense_id = 0;
            $this->show_expense_modal = false;
            $this->selected_expenses = [];
        }
    }

    public function updatedBranchId(): void
    {
        if ($this->branch_id < 0) {
            $this->branch_id = 0;
        }

        $this->selected_expenses = [];
    }

    public function save(): void
    {
        $this->syncAuthContext();

        $data = $this->validate();

        DB::transaction(function () use ($data) {
            $expense = Expense::query()->create([
                'expense_no' => 'EX-' . strtoupper(Str::random(10)),
                'branch_id' => (int) $data['branch_id'],
                'user_id' => auth()->id(),
                'expense_date' => $data['expense_date'],
                'amount' => (float) $data['amount'],
                'payment_method' => $data['payment_method'],
                'expense_type' => ($data['expense_type'] ?? null) ?: null,
                'description' => $data['description'] ?: null,
                'notes' => $data['notes'] ?: null,
            ]);

            ActivityLogger::log(
                'expense.recorded',
                $expense,
                'Expense recorded',
                [
                    'branch_id' => (int) $expense->branch_id,
                    'expense_id' => (int) $expense->id,
                    'amount' => (float) $expense->amount,
                    'payment_method' => (string) $expense->payment_method,
                    'expense_type' => $expense->expense_type,
                ],
                (int) $expense->branch_id
            );

            $this->selected_expense_id = (int) $expense->id;
        });

        $this->amount = null;
        $this->expense_type = null;
        $this->description = null;
        $this->notes = null;
        $this->payment_method = 'cash';

        session()->flash('status', __('Expense recorded successfully.'));

        if ($this->mode !== 'add') {
            $this->mode = 'add';
        }
    }

    public function openExpenseModal(int $expenseId): void
    {
        $this->syncAuthContext();

        $expense = Expense::query()->with(['branch', 'user', 'voidedBy'])->findOrFail($expenseId);
        $this->authorizeExpense($expense);

        $this->selected_expense_id = (int) $expense->id;
        $this->show_expense_modal = true;
    }

    public function closeExpenseModal(): void
    {
        $this->show_expense_modal = false;
        $this->selected_expense_id = 0;
    }

    public function openEditModal(int $expenseId): void
    {
        $this->syncAuthContext();

        $expense = Expense::query()->findOrFail($expenseId);
        $this->authorizeExpense($expense);

        if ($expense->voided_at !== null) {
            throw ValidationException::withMessages([
                'expense' => __('Cannot edit a voided expense.'),
            ]);
        }

        $this->editing_expense_id = (int) $expense->id;
        $this->edit_branch_id = (int) $expense->branch_id;
        $this->edit_expense_date = optional($expense->expense_date)->format('Y-m-d') ?: Carbon::today()->toDateString();
        $this->edit_amount = (string) $expense->amount;
        $this->edit_payment_method = (string) ($expense->payment_method ?: 'cash');
        $this->edit_expense_type = $expense->expense_type;
        $this->edit_description = $expense->description;
        $this->edit_notes = $expense->notes;

        $this->show_edit_modal = true;
    }

    public function closeEditModal(): void
    {
        $this->show_edit_modal = false;
        $this->editing_expense_id = 0;
    }

    public function saveEdit(): void
    {
        $this->syncAuthContext();

        $this->validate([
            'editing_expense_id' => ['required', 'integer', 'min:1'],
            'edit_expense_date' => ['required', 'date'],
            'edit_amount' => ['required', 'numeric', 'min:0'],
            'edit_payment_method' => ['required', 'string', 'in:cash,card,transfer'],
            'edit_expense_type' => ['nullable', 'string', 'max:100'],
            'edit_description' => ['nullable', 'string', 'max:255'],
            'edit_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $expense = Expense::query()->findOrFail((int) $this->editing_expense_id);
        $this->authorizeExpense($expense);

        if ($expense->voided_at !== null) {
            throw ValidationException::withMessages([
                'expense' => __('Cannot edit a voided expense.'),
            ]);
        }

        $before = $expense->only(['expense_date', 'amount', 'payment_method', 'expense_type', 'description', 'notes']);

        DB::transaction(function () use ($expense, $before) {
            $expense->update([
                'expense_date' => $this->edit_expense_date,
                'amount' => (float) $this->edit_amount,
                'payment_method' => $this->edit_payment_method,
                'expense_type' => ($this->edit_expense_type ?? null) ?: null,
                'description' => $this->edit_description ?: null,
                'notes' => $this->edit_notes ?: null,
            ]);

            ActivityLogger::log(
                'expense.updated',
                $expense,
                'Expense updated',
                [
                    'branch_id' => (int) $expense->branch_id,
                    'expense_id' => (int) $expense->id,
                    'before' => $before,
                    'after' => $expense->only(['expense_date', 'amount', 'payment_method', 'expense_type', 'description', 'notes']),
                ],
                (int) $expense->branch_id
            );
        });

        $this->closeEditModal();
        session()->flash('status', __('Expense updated successfully.'));
    }

    public function openVoidModal(int $expenseId): void
    {
        $this->syncAuthContext();

        $expense = Expense::query()->findOrFail($expenseId);
        $this->authorizeExpense($expense);

        if ($expense->voided_at !== null) {
            throw ValidationException::withMessages([
                'expense' => __('Expense is already voided.'),
            ]);
        }

        $this->pending_void_expense_id = (int) $expense->id;
        $this->void_reason = null;
        $this->show_void_modal = true;
    }

    public function closeVoidModal(): void
    {
        $this->show_void_modal = false;
        $this->pending_void_expense_id = 0;
        $this->void_reason = null;
    }

    public function confirmVoidExpense(): void
    {
        $this->syncAuthContext();

        $this->validate([
            'pending_void_expense_id' => ['required', 'integer', 'min:1'],
            'void_reason' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () {
            $expense = Expense::query()->lockForUpdate()->findOrFail((int) $this->pending_void_expense_id);
            $this->authorizeExpense($expense);

            if ($expense->voided_at !== null) {
                throw ValidationException::withMessages([
                    'expense' => __('Expense is already voided.'),
                ]);
            }

            $expense->update([
                'voided_at' => now(),
                'voided_by' => auth()->id(),
                'void_reason' => $this->void_reason ?: null,
            ]);

            ActivityLogger::log(
                'expense.voided',
                $expense,
                'Expense voided',
                [
                    'branch_id' => (int) $expense->branch_id,
                    'expense_id' => (int) $expense->id,
                    'void_reason' => $expense->void_reason,
                ],
                (int) $expense->branch_id
            );
        });

        $this->closeVoidModal();
        session()->flash('status', __('Expense voided successfully.'));
    }

    protected function authorizeExpense(Expense $expense): void
    {
        $user = auth()->user();

        if ($user && $user->role !== 'super_admin') {
            abort_unless((int) ($user->branch_id ?? 0) === (int) $expense->branch_id, 403);
        }
    }

    public function selectAllExpensesForDay(string $day): void
    {
        $this->syncAuthContext();

        $ids = Expense::query()
            ->whereDate('expense_date', $day)
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', (int) $this->branch_id))
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $this->selected_expenses = $ids;
    }

    public function clearSelectedExpenses(): void
    {
        $this->selected_expenses = [];
    }

    public function render()
    {
        $this->syncAuthContext();

        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();

        $selectedExpense = null;
        if ($this->show_expense_modal && $this->selected_expense_id > 0) {
            $selectedExpense = Expense::query()->with(['branch', 'user', 'voidedBy'])->find($this->selected_expense_id);
        }

        $expenses = collect();
        if ($this->mode === 'manage') {
            $q = Expense::query()->with(['branch', 'user']);

            if (! $this->isSuperAdmin) {
                $q->where('branch_id', (int) $this->branch_id);
            } elseif ($this->branch_id > 0) {
                $q->where('branch_id', (int) $this->branch_id);
            }

            $from = Carbon::parse($this->expense_date_from)->startOfDay();
            $to = Carbon::parse($this->expense_date_to)->endOfDay();
            $q->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()]);

            if ($this->expense_status === 'active') {
                $q->whereNull('voided_at');
            } elseif ($this->expense_status === 'voided') {
                $q->whereNotNull('voided_at');
            }

            if (trim($this->expense_search) !== '') {
                $term = '%' . trim($this->expense_search) . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('expense_no', 'like', $term)
                        ->orWhere('payment_method', 'like', $term)
                        ->orWhere('expense_type', 'like', $term)
                        ->orWhere('description', 'like', $term)
                        ->orWhereHas('branch', fn ($qb) => $qb->where('name', 'like', $term))
                        ->orWhereHas('user', fn ($qu) => $qu->where('name', 'like', $term));
                });
            }

            $expenses = $q->orderByDesc('expense_date')->orderByDesc('id')->limit(200)->get();
        }

        return view('livewire.expenses-index', [
            'branches' => $branches,
            'expenses' => $expenses,
            'selectedExpense' => $selectedExpense,
        ]);
    }
}
