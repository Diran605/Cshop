<?php

namespace Database\Seeders;

use App\Models\BulkType;
use App\Models\BulkUnit;
use Illuminate\Database\Seeder;

class BulkTypeSeeder extends Seeder
{
    public function run(): void
    {
        $branches = \App\Models\Branch::all();

        $bulkTypes = [
            ['name' => 'Wholesale Pack', 'units_per_bulk' => 12],
            ['name' => 'Retail Pack', 'units_per_bulk' => 6],
            ['name' => 'Bulk Carton', 'units_per_bulk' => 24],
            ['name' => 'Mega Pack', 'units_per_bulk' => 48],
            ['name' => 'Mini Pack', 'units_per_bulk' => 3],
        ];

        // Create bulk types for each branch
        foreach ($branches as $branch) {
            $branchBulkUnits = \App\Models\BulkUnit::where('branch_id', $branch->id)->get();

            foreach ($bulkTypes as $bulkType) {
                BulkType::create([
                    'name' => $bulkType['name'],
                    'bulk_unit_id' => $branchBulkUnits->isNotEmpty() ? $branchBulkUnits->random()->id : 1,
                    'units_per_bulk' => $bulkType['units_per_bulk'],
                    'description' => 'Seeded bulk type',
                    'branch_id' => $branch->id,
                ]);
            }
        }
    }
}
