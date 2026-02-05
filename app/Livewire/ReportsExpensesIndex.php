<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReportsExpensesIndex extends Component
{
    public int $branch_id = 0;
    public string $date_from;
    public string $date_to;

    public string $expense_status = 'active';
    public string $search = '';

    public bool $isSuperAdmin = false;
    public int $auth_user_id = 0;

    public function mount(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->auth_user_id = (int) ($user?->id ?? 0);

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        } else {
            $this->branch_id = 0;
        }

        $today = Carbon::today();
        $this->date_from = $today->copy()->startOfMonth()->toDateString();
        $this->date_to = $today->toDateString();

        $this->expense_status = 'active';
        $this->search = '';
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;

            $today = Carbon::today();
            $this->date_from = $today->copy()->startOfMonth()->toDateString();
            $this->date_to = $today->toDateString();

            $this->expense_status = 'active';
            $this->search = '';
        }

        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
    }

    public function render()
    {
        $this->syncAuthContext();

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) (auth()->user()?->branch_id ?? 0);
            $branches = Branch::query()
                ->whereKey($this->branch_id)
                ->where('is_active', true)
                ->get();
        } else {
            $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();
        }

        $from = Carbon::parse($this->date_from)->startOfDay();
        $to = Carbon::parse($this->date_to)->endOfDay();

        $expensesBase = Expense::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->whereBetween('expense_date', [$from->toDateString(), $to->toDateString()]);

        if ($this->expense_status === 'active') {
            $expensesBase->whereNull('voided_at');
        } elseif ($this->expense_status === 'voided') {
            $expensesBase->whereNotNull('voided_at');
        }

        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $expensesBase->where(function ($q) use ($term) {
                $q->where('expense_no', 'like', $term)
                    ->orWhere('payment_method', 'like', $term)
                    ->orWhere('expense_type', 'like', $term)
                    ->orWhere('description', 'like', $term);
            });
        }

        $summaryRow = (clone $expensesBase)
            ->select([
                DB::raw('COUNT(*) as expense_count'),
                DB::raw('SUM(amount) as expense_total'),
            ])
            ->first();

        $expenseCount = (int) ($summaryRow?->expense_count ?? 0);
        $expenseTotal = (float) ($summaryRow?->expense_total ?? 0);

        $expensesByType = (clone $expensesBase)
            ->groupBy('expense_type')
            ->orderByDesc('amount_total')
            ->limit(20)
            ->get([
                DB::raw('COALESCE(expense_type, "Uncategorized") as expense_type'),
                DB::raw('COUNT(*) as expense_count'),
                DB::raw('SUM(amount) as amount_total'),
            ]);

        $expensesByDay = (clone $expensesBase)
            ->groupBy(DB::raw('DATE(expense_date)'))
            ->orderBy(DB::raw('DATE(expense_date)'))
            ->get([
                DB::raw('DATE(expense_date) as day'),
                DB::raw('COUNT(*) as expense_count'),
                DB::raw('SUM(amount) as amount_total'),
            ]);

        return view('livewire.reports-expenses-index', [
            'branches' => $branches,
            'expenseCount' => $expenseCount,
            'expenseTotal' => $expenseTotal,
            'expensesByType' => $expensesByType,
            'expensesByDay' => $expensesByDay,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }
}
