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

        return [
            'adjustment_type' => $adjustmentType,
            'current_stock' => $currentStock,
            'adjustment_quantity' => $adjustmentQuantity,
            'target_stock' => $targetStock,
            'status' => $status,
            'reason' => fake()->sentence(),
            'reviewed_at' => $status !== 'pending' ? fake()->dateTimeBetween('-1 month', 'now') : null,
            'rejection_reason' => $status === 'rejected' ? fake()->sentence() : null,
            'source_type' => fake()->randomElement(['stock_in_receipt', 'sales_receipt', null]),
            'source_id' => fake()->optional()->numberBetween(1, 100),
        ];
    }
}
