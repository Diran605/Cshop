<?php

namespace Database\Factories;

use App\Models\StockInReceipt;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockInReceiptFactory extends Factory
{
    protected $model = StockInReceipt::class;

    public function definition(): array
    {
        return [
            'receipt_no' => 'STK-' . fake()->unique()->numerify('######'),
            'received_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'notes' => fake()->optional()->sentence(),
            'total_quantity' => fake()->numberBetween(10, 500),
            'total_cost' => fake()->randomFloat(2, 1000, 50000),
        ];
    }
}
