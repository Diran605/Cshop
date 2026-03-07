<?php

namespace Database\Factories;

use App\Models\StockInItem;
use App\Models\StockInReceipt;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockInItemFactory extends Factory
{
    protected $model = StockInItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 50);
        $costPrice = fake()->randomFloat(2, 100, 5000);

        return [
            'quantity' => $quantity,
            'remaining_quantity' => fake()->numberBetween(0, $quantity),
            'cost_price' => $costPrice,
            'line_total' => $quantity * $costPrice,
            'supplier_name' => fake()->company(),
            'batch_ref_no' => fake()->optional()->lexify('BATCH-????-???'),
            'expiry_date' => fake()->optional()->dateTimeBetween('now', '+2 years'),
            'entry_mode' => 'unit',
        ];
    }
}
