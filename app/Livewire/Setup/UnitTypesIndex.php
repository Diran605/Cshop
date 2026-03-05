<?php

namespace App\Livewire\Setup;

use App\Models\UnitType;
use App\Models\Branch;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UnitTypesIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $show_modal = false;
    public ?int $editingId = null;
    public string $name = '';
    public bool $is_active = true;
    public int $branch_id = 0;

    public bool $isSuperAdmin = false;
    public int $auth_user_id = 0;

    // Delete modal properties
    public bool $show_delete_modal = false;
    public ?int $pending_delete_id = null;
    public string $pending_delete_name = '';

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('unit_types', 'name')
                    ->where('branch_id', $this->branch_id)
                    ->ignore($this->editingId),
            ],
            'is_active' => ['boolean'],
            'branch_id' => ['required', 'integer', 'min:1'],
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

    public function save(): void
    {
        $this->syncAuthContext();

        if (! $this->isSuperAdmin) {
            $this->branch_id = (int) (auth()->user()?->branch_id ?? 0);
        }

        $data = $this->validate();

        if ($this->editingId) {
            UnitType::query()->whereKey($this->editingId)->update([
                'name' => $data['name'],
                'is_active' => $data['is_active'],
            ]);
            session()->flash('status', 'Unit type updated successfully.');
        } else {
            UnitType::query()->create([
                'branch_id' => $data['branch_id'],
                'name' => $data['name'],
                'is_active' => $data['is_active'],
            ]);
            session()->flash('status', 'Unit type created successfully.');
        }

        $this->show_modal = false;
        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $this->syncAuthContext();

        $unitType = UnitType::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        if ($this->isSuperAdmin) {
            $this->branch_id = (int) ($unitType->branch_id ?? $this->branch_id);
        }

        $this->editingId = $unitType->id;
        $this->name = $unitType->name;
        $this->is_active = (bool) $unitType->is_active;
        $this->show_modal = true;
    }

    public function openModal(): void
    {
        $this->resetForm();
        $this->show_modal = true;
    }

    public function closeModal(): void
    {
        $this->show_modal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->is_active = true;
        $this->resetErrorBag();
    }

    public function delete(int $id): void
    {
        $this->syncAuthContext();

        $unitType = UnitType::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($id);

        $this->pending_delete_id = $unitType->id;
        $this->pending_delete_name = $unitType->name;
        $this->show_delete_modal = true;
    }

    public function confirmDelete(): void
    {
        $unitType = UnitType::query()
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', (int) (auth()->user()?->branch_id ?? 0)))
            ->findOrFail($this->pending_delete_id);

        $unitType->delete();

        $this->show_delete_modal = false;
        $this->pending_delete_id = null;
        $this->pending_delete_name = '';

        session()->flash('status', 'Unit type deleted successfully.');
    }

    public function closeDeleteModal(): void
    {
        $this->show_delete_modal = false;
        $this->pending_delete_id = null;
        $this->pending_delete_name = '';
    }

    public function render()
    {
        $this->syncAuthContext();

        $unitTypes = UnitType::query()
            ->with(['branch'])
            ->when(! $this->isSuperAdmin, fn ($q) => $q->where('branch_id', $this->branch_id))
            ->when(trim($this->search) !== '', function ($q) {
                $term = '%' . trim($this->search) . '%';
                $q->where('name', 'like', $term);
            })
            ->orderBy('name')
            ->paginate(20);

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.settings.unit-types-index', [
            'unitTypes' => $unitTypes,
            'branches' => $branches,
        ]);
    }
}
