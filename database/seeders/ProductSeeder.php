<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $categories = \App\Models\Category::all();
        $unitTypes = \App\Models\UnitType::all();
        $bulkTypes = \App\Models\BulkType::all();

        // Create 20 products per branch
        foreach ($branches as $branch) {
            $branchCategories = $categories->where('branch_id', $branch->id)->push($categories->whereNull('branch_id'))->flatten();
            $branchBulkTypes = $bulkTypes->where('branch_id', $branch->id)->push($bulkTypes->whereNull('branch_id'))->flatten();

            for ($i = 0; $i < 20; $i++) {
                $product = Product::factory()->create([
                    'branch_id' => $branch->id,
                    'category_id' => $branchCategories->isNotEmpty() ? $branchCategories->random()->id : null,
                    'unit_type_id' => $unitTypes->isNotEmpty() ? $unitTypes->random()->id : null,
                    'bulk_type_id' => fake()->boolean(30) && $branchBulkTypes->isNotEmpty() ? $branchBulkTypes->random()->id : null,
                ]);

                // Create product stock
                ProductStock::factory()->create([
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                ]);
            }
        }
    }
}
