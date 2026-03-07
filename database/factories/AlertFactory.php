<?php

namespace Database\Factories;

use App\Models\Alert;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertFactory extends Factory
{
    protected $model = Alert::class;

    public function definition(): array
    {
        $alerts = [
            'stock_adjustment' => [
                ['title' => 'Stock Adjustment Required', 'message' => 'Inventory discrepancy detected for product'],
                ['title' => 'Manual Stock Correction', 'message' => 'Stock count mismatch needs approval'],
            ],
            'expired_stock' => [
                ['title' => 'Expired Stock Alert', 'message' => 'Products have exceeded expiration date'],
                ['title' => 'Expired Items Found', 'message' => 'Remove expired items from inventory'],
            ],
            'expiry_warning' => [
                ['title' => 'Expiry Warning', 'message' => 'Products expiring within 30 days'],
                ['title' => 'Approaching Expiry', 'message' => 'Consider discounting near-expiry items'],
            ],
            'low_stock' => [
                ['title' => 'Low Stock Alert', 'message' => 'Product stock below minimum threshold'],
                ['title' => 'Reorder Required', 'message' => 'Stock level critical, reorder needed'],
            ],
        ];

        $type = fake()->randomElement(['stock_adjustment', 'expired_stock', 'expiry_warning', 'low_stock']);
        $alert = fake()->randomElement($alerts[$type]);

        return [
            'type' => $type,
            'title' => $alert['title'],
            'message' => $alert['message'],
            'metadata' => null,
            'is_read' => fake()->boolean(30),
            'read_at' => fake()->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
