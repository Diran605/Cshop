<?php

namespace Database\Seeders;

use App\Models\UnitType;
use Illuminate\Database\Seeder;

class UnitTypeSeeder extends Seeder
{
    public function run(): void
    {
        $unitTypes = [
            'Piece',
            'Kilogram',
            'Liter',
            'Box',
            'Pack',
            'Dozen',
            'Carton',
        ];

        foreach ($unitTypes as $unitType) {
            UnitType::create(['name' => $unitType]);
        }
    }
}
