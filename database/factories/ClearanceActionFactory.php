<?php

namespace Database\Factories;

use App\Models\ClearanceAction;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClearanceActionFactory extends Factory
{
    protected $model = ClearanceAction::class;

    public function definition(): array
    {
        $originalValue = fake()->randomFloat(2, 1000, 10000);
        $actionValue = fake()->randomFloat(2, 500, $originalValue);
        $recoveredValue = fake()->randomFloat(2, 0, $actionValue);

        $notes = [
            'discount' => 'Applied clearance discount for near-expiry items',
            'donate' => 'Donated to local charity organization',
            'dispose' => 'Disposed according to health regulations',
            'sold' => 'Sold at discounted price',
        ];

        $actionType = fake()->randomElement(['discount', 'donate', 'dispose', 'sold']);

        return [
            'action_type' => $actionType,
            'quantity' => fake()->numberBetween(1, 50),
            'original_value' => $originalValue,
            'action_value' => $actionValue,
            'recovered_value' => $recoveredValue,
            'loss_value' => $originalValue - $recoveredValue,
            'notes' => $notes[$actionType],
            'metadata' => null,
        ];
    }
}
