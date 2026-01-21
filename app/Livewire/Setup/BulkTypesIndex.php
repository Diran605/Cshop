<?php

namespace App\Livewire\Setup;

use App\Models\BulkType;
use App\Models\BulkUnit;
use Livewire\Component;

class BulkTypesIndex extends Component
{
    public string $name = '';
    public int $bulk_unit_id = 0;
    public int $units_per_bulk = 1;
    public ?string $description = null;
    public ?int $editingId = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'bulk_unit_id' => ['required', 'integer', 'min:1'],
            'units_per_bulk' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function mount(): void
    {
        $this->bulk_unit_id = (int) (BulkUnit::query()->orderBy('name')->value('id') ?? 0);
    }

    public function save(): void
    {
        $data = $this->validate();

        if ($this->editingId) {
            BulkType::query()->whereKey($this->editingId)->update($data);
        } else {
            BulkType::query()->create($data);
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $type = BulkType::query()->findOrFail($id);
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
        BulkType::query()->findOrFail($id)->forceDelete();
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'units_per_bulk', 'description', 'editingId']);

        $this->bulk_unit_id = (int) (BulkUnit::query()->orderBy('name')->value('id') ?? 0);
        $this->units_per_bulk = 1;
    }

    public function render()
    {
        $bulkUnits = BulkUnit::query()->orderBy('name')->get();

        $bulkTypes = BulkType::query()
            ->with(['bulkUnit'])
            ->orderBy('name')
            ->get();

        return view('livewire.setup.bulk-types-index', [
            'bulkUnits' => $bulkUnits,
            'bulkTypes' => $bulkTypes,
        ]);
    }
}
