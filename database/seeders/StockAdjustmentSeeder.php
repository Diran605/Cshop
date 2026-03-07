<?php

namespace Database\Seeders;

use App\Models\StockAdjustment;
use App\Models\Branch;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;

class StockAdjustmentSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $users = User::where('role', '!=', 'super_admin')->get();

        foreach ($branches as $branch) {
            $branchUsers = $users->where('branch_id', $branch->id);
            $products = Product::where('branch_id', $branch->id)->get();

            // Create 100 stock adjustments per branch
            for ($i = 0; $i < 100; $i++) {
                $product = $products->random();
                $status = fake()->randomElement(['pending', 'approved', 'approved', 'approved', 'rejected']);

                StockAdjustment::factory()->create([
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                    'status' => $status,
                    'requested_by' => $branchUsers->random()->id,
                    'reviewed_by' => $status !== 'pending' ? $branchUsers->random()->id : null,
                    'reviewed_at' => $status !== 'pending' ? fake()->dateTimeBetween('-1 month', 'now') : null,
                    'rejection_reason' => $status === 'rejected' ? fake()->sentence() : null,
                ]);
            }
        }
    }
}
