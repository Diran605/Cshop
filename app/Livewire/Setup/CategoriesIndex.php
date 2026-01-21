<?php

namespace App\Livewire\Setup;

use App\Models\Category;
use Livewire\Component;

class CategoriesIndex extends Component
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
            Category::query()->whereKey($this->editingId)->update($data);
        } else {
            Category::query()->create($data);
        }

        $this->resetForm();
    }

    public function edit(int $id): void
    {
        $category = Category::query()->findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->description = $category->description;
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        Category::query()->findOrFail($id)->forceDelete();
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->reset(['name', 'description', 'editingId']);
    }

    public function render()
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('livewire.setup.categories-index', [
            'categories' => $categories,
        ]);
    }
}
