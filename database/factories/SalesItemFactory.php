<?php

namespace Database\Factories;

use App\Models\SalesItem;
use App\Models\SalesReceipt;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesItemFactory extends Factory
{
    protected $model = SalesItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 10);
        $unitPrice = fake()->randomFloat(2, 200, 5000);
        $lineTotal = $quantity * $unitPrice;
        $unitCost = fake()->randomFloat(2, 100, $unitPrice);
        $lineCost = $quantity * $unitCost;

        return [
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'unit_cost' => $unitCost,
            'line_cost' => $lineCost,
            'line_profit' => $lineTotal - $lineCost,
            'entry_mode' => 'unit',
            'is_low_profit' => fake()->boolean(20),
            'is_loss' => fake()->boolean(5),
        ];
    }
}
