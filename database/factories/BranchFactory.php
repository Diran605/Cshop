<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        $branches = [
            'Downtown Store',
            'Mall Outlet',
            'Central Branch',
            'Westside Location',
            'Eastside Plaza',
            'Northgate Center',
            'Southpark Store',
            'Harbor View Branch',
            'University District',
            'Airport Terminal Store',
        ];

        return [
            'name' => fake()->unique()->randomElement($branches),
            'code' => strtoupper(fake()->lexify('BR-???')),
            'is_active' => true,
        ];
    }
}
