<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\UnitType;
use App\Models\BulkType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'cost_price' => fake()->randomFloat(2, 100, 10000),
            'min_selling_price' => fake()->randomFloat(2, 150, 15000),
            'selling_price' => fake()->randomFloat(2, 200, 20000),
            'bulk_enabled' => fake()->boolean(30),
            'status' => fake()->randomElement(['active', 'active', 'active', 'active', 'inactive']),
        ];
    }
}
