<?php

namespace Database\Seeders;

use App\Models\StockInReceipt;
use App\Models\StockInItem;
use App\Models\StockAdjustment;
use App\Models\Branch;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;

class StockInSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $users = User::where('role', '!=', 'super_admin')->get();

        foreach ($branches as $branch) {
            $branchUsers = $users->where('branch_id', $branch->id);

            // Create 100 stock-in receipts per branch
            for ($i = 0; $i < 100; $i++) {
                $receipt = StockInReceipt::factory()->create([
                    'branch_id' => $branch->id,
                    'user_id' => $branchUsers->random()->id,
                ]);

                // Create 3-10 items per receipt
                $itemCount = fake()->numberBetween(3, 10);
                $products = Product::where('branch_id', $branch->id)->inRandomOrder()->take($itemCount)->get();

                foreach ($products as $product) {
                    StockInItem::factory()->create([
                        'stock_in_receipt_id' => $receipt->id,
                        'product_id' => $product->id,
                    ]);
                }

                // 20% chance to be voided
                if (fake()->boolean(20)) {
                    $this->voidStockIn($receipt, $branch);
                }
            }
        }
    }

    private function voidStockIn($receipt, $branch): void
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
                'adjustment_type' => StockAdjustment::TYPE_STOCK_IN_VOID,
                'status' => 'approved',
                'source_type' => 'stock_in_receipt',
                'source_id' => $receipt->id,
                'requested_by' => $voidedBy->id,
                'reviewed_by' => $voidedBy->id,
            ]);
        }
    }
}
