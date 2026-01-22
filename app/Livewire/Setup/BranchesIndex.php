<?php

namespace App\Livewire\Setup;

use App\Models\Branch;
use Livewire\Component;

class BranchesIndex extends Component
{
    public int $editingId = 0;
    public string $name = '';
    public ?string $code = null;
    public bool $is_active = true;

    public string $search = '';

    public bool $show_edit_modal = false;

    public bool $show_delete_modal = false;
    public int $pending_delete_id = 0;
    public string $pending_delete_name = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ];
    }

    public function resetForm(): void
    {
        $this->editingId = 0;
        $this->name = '';
        $this->code = null;
        $this->is_active = true;
        $this->search = '';
        $this->show_edit_modal = false;
        $this->resetErrorBag();
    }

    public function edit(int $id): void
    {
        $branch = Branch::query()->findOrFail($id);

        $this->editingId = (int) $branch->id;
        $this->name = (string) $branch->name;
        $this->code = $branch->code !== null ? (string) $branch->code : null;
        $this->is_active = (bool) $branch->is_active;
    }

    public function openEditModal(int $id): void
    {
        $this->edit($id);
        $this->show_edit_modal = true;
    }

    public function closeEditModal(): void
    {
        $this->show_edit_modal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId > 0) {
            Branch::query()->whereKey($this->editingId)->update([
                'name' => $data['name'],
                'code' => $data['code'] ?? null,
                'is_active' => (bool) $data['is_active'],
            ]);

            session()->flash('status', 'Branch updated successfully.');
            $this->resetForm();
            return;
        }

        Branch::query()->create([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'is_active' => (bool) $data['is_active'],
        ]);

        session()->flash('status', 'Branch created successfully.');
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $branch = Branch::query()->findOrFail($id);
        $branch->delete();

        if ($this->editingId === (int) $id) {
            $this->resetForm();
        }

        session()->flash('status', 'Branch deleted successfully.');
    }

    public function openDeleteModal(int $id): void
    {
        $branch = Branch::query()->findOrFail($id);

        $this->pending_delete_id = (int) $branch->id;
        $this->pending_delete_name = (string) $branch->name;
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

    public function render()
    {
        return view('livewire.setup.branches-index', [
            'branches' => Branch::query()
                ->when(trim($this->search) !== '', function ($q) {
                    $term = '%' . trim($this->search) . '%';
                    $q->where(function ($qq) use ($term) {
                        $qq->where('name', 'like', $term)
                            ->orWhere('code', 'like', $term);
                    });
                })
                ->orderBy('name')
                ->get(),
        ]);
    }
}
