<?php

namespace Database\Factories;

use App\Models\DonationItem;
use App\Models\Donation;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationItemFactory extends Factory
{
    protected $model = DonationItem::class;

    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 50);
        $unitValue = fake()->randomFloat(2, 100, 5000);

        return [
            'quantity' => $quantity,
            'unit_value' => $unitValue,
            'total_value' => $quantity * $unitValue,
            'expiry_date' => fake()->optional()->dateTimeBetween('now', '+1 year'),
        ];
    }
}
