<?php

namespace App\Livewire\Setup;

use App\Models\Branch;
use App\Models\BulkType;
use App\Models\BulkUnit;
use Illuminate\Validation\Rule;
use Livewire\Component;

class BulkTypesIndex extends Component
{
    public int $branch_id = 0;
    public string $search = '';
    public string $name = '';
    public int $bulk_unit_id = 0;
    public int $units_per_bulk = 1;
    public ?string $description = null;
    public ?int $editingId = null;

    public bool $show_delete_modal = false;
    public int $pending_delete_id = 0;
    public string $pending_delete_name = '';

    public bool $isSuperAdmin = false;
    public int $auth_user_id = 0;

    protected function rules(): array
    {
        return [
            'branch_id' => ['required', 'integer', 'min:1'],
            'name' => ['required', 'string', 'max:255'],
            'bulk_unit_id' => ['required', 'integer', 'min:1', Rule::exists('bulk_units', 'id')->where('branch_id', $this->branch_id)],
            'units_per_bulk' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function mount(): void
    {
        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        $this->auth_user_id = (int) ($user?->id ?? 0);

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        } else {
            $this->branch_id = (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        }

        $this->bulk_unit_id = (int) (BulkUnit::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->value('id') ?? 0);
    }

    protected function syncAuthContext(): void
    {
        $user = auth()->user();
        $currentUserId = (int) ($user?->id ?? 0);

        if ($currentUserId !== $this->auth_user_id) {
            $this->auth_user_id = $currentUserId;
            $this->resetForm();
        }

        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        }
    }

    public function updatedBranchId(): void
    {
        if (! $this->isSuperAdmin || $this->editingId) {
            return;
        }

        $this->bulk_unit_id = (int) (BulkUnit::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->value('id') ?? 0);
    }

    public function save(): void
    {
        $this->syncAuthContext();

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) (auth()->user()?->branch_id ?? 0);
        }

        $data = $this->validate();

        $data['branch_id'] = (int) $this->branch_id;

        if ($this->editingId) {
            BulkType::query()
                ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
                ->whereKey($this->editingId)
                ->update($data);
        } else {
            BulkType::query()->create($data);
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $this->syncAuthContext();

        $type = BulkType::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        if ($this->isSuperAdmin) {
            $this->branch_id = (int) ($type->branch_id ?? $this->branch_id);
        }
        $this->editingId = $type->id;
        $this->name = $type->name;
        $this->bulk_unit_id = (int) $type->bulk_unit_id;
        $this->units_per_bulk = (int) $type->units_per_bulk;
        $this->description = $type->description;
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $this->syncAuthContext();

        $type = BulkType::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);
        $type->forceDelete();
        $this->resetForm();
    }

    public function openDeleteModal(int $id): void
    {
        $this->syncAuthContext();

        $type = BulkType::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        $this->pending_delete_id = (int) $type->id;
        $this->pending_delete_name = (string) $type->name;
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
        $id = (int) $this->pending_delete_id;
        $this->closeDeleteModal();

        if ($id > 0) {
            $this->delete($id);
        }
    }

    private function resetForm(): void
    {
        $currentBranchId = (int) $this->branch_id;

        $this->reset(['search', 'name', 'units_per_bulk', 'description', 'editingId']);

        $user = auth()->user();
        $this->isSuperAdmin = (bool) ($user?->role === 'super_admin');
        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) ($user?->branch_id ?? 0);
        } else {
            $this->branch_id = $currentBranchId > 0
                ? $currentBranchId
                : (int) (Branch::query()->where('is_active', true)->orderBy('name')->value('id') ?? 0);
        }

        $this->bulk_unit_id = (int) (BulkUnit::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->value('id') ?? 0);
        $this->units_per_bulk = 1;
    }

    public function render()
    {
        $this->syncAuthContext();

        $branches = Branch::query()->where('is_active', true)->orderBy('name')->get();

        $bulkUnits = BulkUnit::query()
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->orderBy('name')
            ->get();

        $bulkTypes = BulkType::query()
            ->with(['bulkUnit', 'branch'])
            ->when($this->branch_id > 0, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where(function ($qq) use ($term) {
                    $qq->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term);
                });
            })
            ->orderBy('name')
            ->get();

        return view('livewire.setup.bulk-types-index', [
            'branches' => $branches,
            'bulkUnits' => $bulkUnits,
            'bulkTypes' => $bulkTypes,
            'isSuperAdmin' => $this->isSuperAdmin,
        ]);
    }
}
