<?php

namespace Database\Seeders;

use App\Models\Disposal;
use App\Models\DisposalItem;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DisposalSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $users = User::where('role', '!=', 'super_admin')->get();

        foreach ($branches as $branch) {
            $branchUsers = $users->where('branch_id', $branch->id);
            $products = Product::where('branch_id', $branch->id)->get();

            // Create 20 disposals per branch
            for ($i = 0; $i < 20; $i++) {
                $disposal = Disposal::factory()->create([
                    'branch_id' => $branch->id,
                    'user_id' => $branchUsers->random()->id,
                ]);

                // Create 1-5 disposal items
                $itemCount = fake()->numberBetween(1, 5);
                for ($j = 0; $j < $itemCount; $j++) {
                    DisposalItem::factory()->create([
                        'disposal_id' => $disposal->id,
                        'product_id' => $products->random()->id,
                    ]);
                }
            }
        }
    }
}
