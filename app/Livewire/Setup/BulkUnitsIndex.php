<?php

namespace App\Livewire\Setup;

use App\Models\BulkUnit;
use Livewire\Component;

class BulkUnitsIndex extends Component
{
    public string $name = '';
    public ?string $description = null;
    public ?int $editingId = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            BulkUnit::query()->whereKey($this->editingId)->update($data);
        } else {
            BulkUnit::query()->create($data);
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $unit = BulkUnit::query()->findOrFail($id);
        $this->editingId = $unit->id;
        $this->name = $unit->name;
        $this->description = $unit->description;
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        BulkUnit::query()->findOrFail($id)->forceDelete();
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'description', 'editingId']);
    }

    public function render()
    {
        $bulkUnits = BulkUnit::query()
            ->orderBy('name')
            ->get();

        return view('livewire.setup.bulk-units-index', [
            'bulkUnits' => $bulkUnits,
        ]);
    }
}
