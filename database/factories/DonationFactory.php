<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        return [
            'organization_name' => fake()->company(),
            'organization_contact' => fake()->optional()->phoneNumber(),
            'organization_address' => fake()->optional()->address(),
            'total_items' => fake()->numberBetween(1, 50),
            'total_value' => fake()->randomFloat(2, 1000, 50000),
            'receipt_number' => 'DON-' . fake()->unique()->numerify('######'),
            'notes' => fake()->optional()->sentence(),
            'donated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
