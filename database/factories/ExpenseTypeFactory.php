<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\ExpenseType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseTypeFactory extends Factory
{
    protected $model = ExpenseType::class;

    public function definition(): array
    {
        $types = [
            'Rent', 'Utilities', 'Salaries', 'Supplies', 'Marketing',
            'Transportation', 'Maintenance', 'Insurance', 'Taxes', 'Miscellaneous',
        ];

        return [
            'branch_id' => Branch::query()->inRandomOrder()->value('id'),
            'name' => fake()->randomElement($types),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function forBranch(int $branchId): static
    {
        return $this->state(fn (array $attributes) => [
            'branch_id' => $branchId,
        ]);
    }
}
