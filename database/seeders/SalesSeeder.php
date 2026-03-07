<?php

namespace Database\Seeders;

use App\Models\SalesReceipt;
use App\Models\SalesItem;
use App\Models\StockAdjustment;
use App\Models\Branch;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $users = User::where('role', '!=', 'super_admin')->get();

        foreach ($branches as $branch) {
            $branchUsers = $users->where('branch_id', $branch->id);
            $products = Product::where('branch_id', $branch->id)->where('status', 'active')->get();

            // Create 20 sales receipts per branch
            for ($i = 0; $i < 20; $i++) {
                $receipt = SalesReceipt::factory()->create([
                    'branch_id' => $branch->id,
                    'user_id' => $branchUsers->random()->id,
                ]);

                // Create 1-5 items per sale
                $itemCount = fake()->numberBetween(1, 5);
                $saleProducts = $products->random(min($itemCount, $products->count()));

                foreach ($saleProducts as $product) {
                    SalesItem::factory()->create([
                        'sales_receipt_id' => $receipt->id,
                        'product_id' => $product->id,
                    ]);
                }

                // 15% chance to be voided
                if (fake()->boolean(15)) {
                    $this->voidSale($receipt, $branch);
                }
            }
        }
    }

    private function voidSale($receipt, $branch): void
    {
        $voidedBy = User::where('branch_id', $branch->id)->inRandomOrder()->first();

        // Mark receipt as voided
        $receipt->update([
            'voided_at' => now(),
            'voided_by' => $voidedBy->id,
            'void_reason' => 'Test void - seeder data',
        ]);

        // Create stock adjustments for void
        foreach ($receipt->items as $item) {
            StockAdjustment::factory()->create([
                'branch_id' => $branch->id,
                'product_id' => $item->product_id,
                'adjustment_type' => StockAdjustment::TYPE_SALES_VOID,
                'status' => 'approved',
                'source_type' => 'sales_receipt',
                'source_id' => $receipt->id,
                'requested_by' => $voidedBy->id,
                'reviewed_by' => $voidedBy->id,
            ]);
        }
    }
}
