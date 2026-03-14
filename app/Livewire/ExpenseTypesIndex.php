<?php

namespace App\Livewire;

use App\Models\Branch;
use App\Models\ExpenseType;
use App\Support\ActivityLogger;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ExpenseTypesIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public int $branch_id = 0;

    public bool $isSuperAdmin = false;
    public int $auth_user_id = 0;

    // Create/Edit modal
    public bool $show_modal = false;
    public int $editing_id = 0;
    public string $expense_type_name = '';
    public ?string $expense_type_description = null;
    public bool $expense_type_is_active = true;

    protected $paginationTheme = 'tailwind';

    protected function rules(): array
    {
        return [
            'expense_type_name' => ['required', 'string', 'max:100'],
            'expense_type_description' => ['nullable', 'string', 'max:500'],
            'expense_type_is_active' => ['boolean'],
        ];
    }

    public function mount(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->auth_user_id = (int) ($user?->id ?? 0);

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user->branch_id ?? 0);
        } else {
            $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        }
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;
        }

        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user->branch_id ?? 0);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingBranchId(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->show_modal = true;
    }

    public function openEditModal(int $id): void
    {
        $this->syncAuthContext();

        $expenseType = ExpenseType::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->findOrFail($id);

        $this->editing_id = $expenseType->id;
        $this->expense_type_name = $expenseType->name;
        $this->expense_type_description = $expenseType->description;
        $this->expense_type_is_active = (bool) $expenseType->is_active;

        $this->show_modal = true;
    }

    public function save(): void
    {
        $this->validate();

        $this->syncAuthContext();

        DB::transaction(function () {
            $data = [
                'branch_id' => $this->branch_id,
                'name' => trim($this->expense_type_name),
                'description' => $this->expense_type_description ?: null,
                'is_active' => $this->expense_type_is_active,
            ];

            if ($this->editing_id > 0) {
                $expenseType = ExpenseType::query()
                    ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
                    ->findOrFail($this->editing_id);

                $expenseType->update($data);

                ActivityLogger::log(
                    'expense_type.updated',
                    $expenseType,
                    "Updated expense type: {$expenseType->name}",
                    ['name' => $expenseType->name],
                    $this->branch_id
                );
            } else {
                $expenseType = ExpenseType::query()->create($data);

                ActivityLogger::log(
                    'expense_type.created',
                    $expenseType,
                    "Created expense type: {$expenseType->name}",
                    ['name' => $expenseType->name],
                    $this->branch_id
                );
            }
        });

        $this->show_modal = false;
        $this->resetForm();
        session()->flash('status', $this->editing_id > 0 ? 'Expense type updated.' : 'Expense type created.');
    }

    // Delete modal
    public bool $show_delete_modal = false;
    public int $pending_delete_id = 0;
    public string $pending_delete_name = '';

    public function openDeleteModal(int $id): void
    {
        $expenseType = ExpenseType::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->find($id);

        if (! $expenseType) {
            return;
        }

        $this->pending_delete_id = $id;
        $this->pending_delete_name = $expenseType->name;
        $this->show_delete_modal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->show_delete_modal = false;
        $this->pending_delete_id = 0;
        $this->pending_delete_name = '';
    }

    public function confirmDelete(): void
    {
        if ($this->pending_delete_id <= 0) {
            return;
        }

        $this->delete($this->pending_delete_id);
        $this->closeDeleteModal();
    }

    public function delete(int $id): void
    {
        if (! auth()->user()?->can('expense_types.void')) {
            session()->flash('error', 'You do not have permission to void expense types.');
            return;
        }

        $this->syncAuthContext();

        $expenseType = ExpenseType::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->findOrFail($id);

        $name = $expenseType->name;

        $expenseType->is_active = false;
        $expenseType->save();

        ActivityLogger::log(
            'expense_type.voided',
            $expenseType,
            "Voided expense type: {$name}",
            ['name' => $name],
            $this->branch_id
        );

        session()->flash('status', 'Expense type voided.');
    }

    public function closeModal(): void
    {
        $this->show_modal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editing_id = 0;
        $this->expense_type_name = '';
        $this->expense_type_description = null;
        $this->expense_type_is_active = true;
        $this->resetErrorBag();
    }

    public function render()
    {
        $this->syncAuthContext();

        $branches = $this->isSuperAdmin
            ? Branch::query()->where('is_active', true)->orderBy('name')->get()
            : collect();

        $expenseTypes = ExpenseType::query()
            ->with(['branch'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->isSuperAdmin && $this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(20);

        return view('livewire.expense-types-index', [
            'branches' => $branches,
            'expenseTypes' => $expenseTypes,
        ]);
    }
}
