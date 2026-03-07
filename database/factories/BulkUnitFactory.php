<?php

namespace Database\Factories;

use App\Models\BulkUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class BulkUnitFactory extends Factory
{
    protected $model = BulkUnit::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Carton', 'Pack', 'Bundle', 'Case', 'Bag']),
            'description' => fake()->optional()->sentence(),
            'branch_id' => null,
        ];
    }
}
