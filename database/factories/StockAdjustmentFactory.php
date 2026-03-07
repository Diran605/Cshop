<?php

namespace Database\Factories;

use App\Models\StockAdjustment;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockAdjustmentFactory extends Factory
{
    protected $model = StockAdjustment::class;

    public function definition(): array
    {
        $adjustmentType = fake()->randomElement([
            StockAdjustment::TYPE_VOID_PRODUCT,
            StockAdjustment::TYPE_STOCK_IN_VOID,
            StockAdjustment::TYPE_SALES_VOID,
            StockAdjustment::TYPE_MANUAL_ADJUSTMENT,
        ]);
        $status = fake()->randomElement(['pending', 'approved', 'approved', 'approved', 'rejected']);
        $adjustmentQuantity = fake()->numberBetween(-50, 50);
        $currentStock = fake()->numberBetween(50, 200);
        $targetStock = $currentStock + $adjustmentQuantity;

        $reasons = [
            'Damaged goods discovered during inventory check',
            'Customer return approved',
            'Supplier overstock correction',
            'Expired items removed from shelf',
            'Theft or loss reported',
            'Receiving error correction',
            'Quality control rejection',
            'Promotional stock adjustment',
            'Transfer from another branch',
            'Inventory recount adjustment',
        ];

        $rejectionReasons = [
            'Insufficient documentation provided',
            'Awaiting manager approval',
            'Requires further investigation',
            'Duplicate adjustment request',
            'Invalid stock count',
        ];

        return [
            'adjustment_type' => $adjustmentType,
            'current_stock' => $currentStock,
            'adjustment_quantity' => $adjustmentQuantity,
            'target_stock' => $targetStock,
            'status' => $status,
            'reason' => fake()->randomElement($reasons),
            'reviewed_at' => $status !== 'pending' ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'rejection_reason' => $status === 'rejected' ? fake()->randomElement($rejectionReasons) : null,
            'source_type' => fake()->randomElement(['stock_in_receipt', 'sales_receipt', null]),
            'source_id' => fake()->optional()->numberBetween(1, 100),
        ];
    }
}
