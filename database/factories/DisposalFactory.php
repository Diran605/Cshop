<?php

namespace Database\Factories;

use App\Models\Disposal;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DisposalFactory extends Factory
{
    protected $model = Disposal::class;

    public function definition(): array
    {
        $reasonDetails = [
            'Product exceeded expiration date by 7 days',
            'Packaging damaged during transport',
            'Quality control failed inspection',
            'Manufacturer recall notice received',
            'Customer complaint investigation',
        ];

        $notes = [
            'Disposed according to health regulations',
            'Documented for insurance claim',
            'Returned to supplier for credit',
            'Incinerated per safety protocol',
            'Donated to local charity after approval',
        ];

        return [
            'disposal_reason' => fake()->randomElement(['expired', 'damaged', 'quality_issue', 'recall', 'other']),
            'reason_details' => fake()->optional()->randomElement($reasonDetails),
            'total_items' => fake()->numberBetween(1, 50),
            'total_loss' => fake()->randomFloat(2, 1000, 50000),
            'disposal_method' => fake()->randomElement(['trash', 'incineration', 'return_to_supplier']),
            'notes' => fake()->optional()->randomElement($notes),
            'disposed_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
