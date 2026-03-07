<?php

namespace Database\Seeders;

use App\Models\ClearanceAction;
use App\Models\ClearanceItem;
use App\Models\ClearanceDiscountRule;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use App\Models\StockInItem;
use Illuminate\Database\Seeder;

class ClearanceSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $users = User::where('role', '!=', 'super_admin')->get();

        foreach ($branches as $branch) {
            $branchUsers = $users->where('branch_id', $branch->id);
            $products = Product::where('branch_id', $branch->id)->get();
            $stockInItems = StockInItem::whereHas('receipt', function ($q) use ($branch) {
                $q->where('branch_id', $branch->id);
            })->get();

            // Create clearance discount rules
            $rules = [];
            $ruleData = [
                ['days_min' => 30, 'days_max' => 60, 'discount' => 10, 'label' => 'Approaching'],
                ['days_min' => 14, 'days_max' => 29, 'discount' => 25, 'label' => 'Urgent'],
                ['days_min' => 0, 'days_max' => 13, 'discount' => 50, 'label' => 'Critical'],
            ];

            foreach ($ruleData as $rule) {
                $rules[] = ClearanceDiscountRule::create([
                    'branch_id' => $branch->id,
                    'days_to_expiry_min' => $rule['days_min'],
                    'days_to_expiry_max' => $rule['days_max'],
                    'discount_percentage' => $rule['discount'],
                    'status_label' => $rule['label'],
                ]);
            }

            // Create clearance items
            $clearanceItems = [];
            for ($i = 0; $i < 10; $i++) {
                if ($stockInItems->isEmpty() || $products->isEmpty()) continue;

                $stockItem = $stockInItems->random();
                $clearanceItems[] = ClearanceItem::create([
                    'branch_id' => $branch->id,
                    'stock_in_item_id' => $stockItem->id,
                    'product_id' => $stockItem->product_id ?? $products->random()->id,
                    'discount_rule_id' => fake()->randomElement($rules)->id ?? null,
                    'expiry_date' => fake()->dateTimeBetween('now', '+60 days'),
                    'days_to_expiry' => fake()->numberBetween(0, 60),
                    'status' => fake()->randomElement(['approaching', 'urgent', 'critical', 'expired']),
                    'quantity' => fake()->numberBetween(1, 20),
                    'original_price' => fake()->randomFloat(2, 100, 5000),
                    'suggested_discount_pct' => fake()->randomFloat(2, 10, 50),
                ]);
            }

            // Create clearance actions
            foreach ($clearanceItems as $clearanceItem) {
                if ($branchUsers->isEmpty()) continue;

                ClearanceAction::factory()->create([
                    'branch_id' => $branch->id,
                    'clearance_item_id' => $clearanceItem->id,
                    'user_id' => $branchUsers->random()->id,
                ]);
            }
        }
    }
}
