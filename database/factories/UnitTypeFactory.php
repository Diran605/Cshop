<?php

namespace Database\Factories;

use App\Models\UnitType;
use Illuminate\Database\Eloquent\Factories\Factory;

class UnitTypeFactory extends Factory
{
    protected $model = UnitType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Piece', 'Kilogram', 'Liter', 'Box', 'Pack', 'Dozen', 'Carton']),
            'branch_id' => null,
            'is_active' => true,
        ];
    }
}
