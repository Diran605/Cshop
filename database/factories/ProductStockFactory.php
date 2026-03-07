<?php

namespace Database\Factories;

use App\Models\ProductStock;
use App\Models\Product;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductStockFactory extends Factory
{
    protected $model = ProductStock::class;

    public function definition(): array
    {
        return [
            'current_stock' => fake()->numberBetween(0, 500),
            'minimum_stock' => fake()->numberBetween(5, 50),
            'cost_price' => fake()->randomFloat(2, 100, 10000),
        ];
    }
}
