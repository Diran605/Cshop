<?php

namespace Database\Factories;

use App\Models\StockMovement;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        $movementType = fake()->randomElement(['IN', 'OUT', 'ADJUSTMENT']);
        $quantity = fake()->numberBetween(1, 50);
        $beforeStock = fake()->numberBetween(10, 200);
        $afterStock = $movementType === 'IN' ? $beforeStock + $quantity : max(0, $beforeStock - $quantity);

        return [
            'movement_type' => $movementType,
            'quantity' => $quantity,
            'before_stock' => $beforeStock,
            'after_stock' => $afterStock,
            'unit_cost' => fake()->randomFloat(2, 100, 5000),
            'unit_price' => fake()->randomFloat(2, 150, 6000),
            'moved_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
