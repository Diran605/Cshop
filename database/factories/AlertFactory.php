<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        return [
            'type' => fake()->randomElement(['stock_adjustment', 'expired_stock', 'expiry_warning', 'low_stock']),
            'title' => fake()->sentence(3),
            'message' => fake()->sentence(),
            'metadata' => null,
            'is_read' => fake()->boolean(30),
            'read_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
