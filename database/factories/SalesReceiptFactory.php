<?php

namespace Database\Factories;

use App\Models\SalesReceipt;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SalesReceiptFactory extends Factory
{
    protected $model = SalesReceipt::class;

    public function definition(): array
    {
        return [
            'receipt_no' => 'SAL-' . fake()->unique()->numerify('######'),
            'sold_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'payment_method' => fake()->randomElement(['cash', 'card', 'mobile', 'transfer']),
            'customer_name' => fake()->name(),
            'sub_total' => fake()->randomFloat(2, 1000, 50000),
            'discount_total' => fake()->randomFloat(2, 0, 5000),
            'grand_total' => fake()->randomFloat(2, 1000, 55000),
            'cogs_total' => fake()->randomFloat(2, 500, 30000),
            'profit_total' => fake()->randomFloat(2, 100, 25000),
            'amount_paid' => fake()->randomFloat(2, 1000, 55000),
            'change_due' => fake()->randomFloat(2, 0, 1000),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
