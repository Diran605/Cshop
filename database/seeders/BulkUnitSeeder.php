<?php

namespace Database\Seeders;

use App\Models\BulkUnit;
use Illuminate\Database\Seeder;

class BulkUnitSeeder extends Seeder
{
    public function run(): void
    {
        $branches = \App\Models\Branch::all();

        $bulkUnits = [
            ['name' => 'Carton', 'description' => 'Large box containing multiple units'],
            ['name' => 'Pack', 'description' => 'Medium pack containing several units'],
            ['name' => 'Bundle', 'description' => 'Grouped items tied together'],
            ['name' => 'Case', 'description' => 'Protective case with multiple items'],
            ['name' => 'Bag', 'description' => 'Bag containing loose items'],
        ];

        // Create bulk units for each branch
        foreach ($branches as $branch) {
            foreach ($bulkUnits as $bulkUnit) {
                BulkUnit::create([
                    'name' => $bulkUnit['name'],
                    'description' => $bulkUnit['description'],
                    'branch_id' => $branch->id,
                ]);
            }
        }
    }
}
