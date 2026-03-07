<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        return [
            'expense_no' => 'EXP-' . fake()->unique()->numerify('######'),
            'expense_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'amount' => fake()->randomFloat(2, 1000, 50000),
            'payment_method' => fake()->randomElement(['cash', 'card', 'transfer']),
            'description' => fake()->sentence(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
