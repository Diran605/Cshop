<?php

namespace Database\Factories;

use App\Models\BulkType;
use App\Models\BulkUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class BulkTypeFactory extends Factory
{
    protected $model = BulkType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Wholesale Pack', 'Retail Pack', 'Bulk Carton', 'Mega Pack', 'Mini Pack']),
            'units_per_bulk' => fake()->numberBetween(2, 50),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
