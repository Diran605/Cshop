<?php

namespace Database\Factories;

use App\Models\DisposalItem;
use App\Models\Disposal;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisposalItemFactory extends Factory
{
    protected $model = DisposalItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 50);
        $unitCost = fake()->randomFloat(2, 100, 5000);

        return [
            'quantity' => $quantity,
            'unit_cost' => $unitCost,
            'total_loss' => $quantity * $unitCost,
            'expiry_date' => fake()->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
