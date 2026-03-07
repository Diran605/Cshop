<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        $descriptions = [
            'created' => 'Record created successfully',
            'updated' => 'Record updated with new information',
            'deleted' => 'Record removed from system',
            'voided' => 'Transaction voided and reversed',
            'approved' => 'Request approved by manager',
            'rejected' => 'Request rejected due to policy',
        ];

        $action = fake()->randomElement(['created', 'updated', 'deleted', 'voided', 'approved', 'rejected']);

        return [
            'action' => $action,
            'subject_type' => fake()->randomElement(['product', 'stock_in_receipt', 'sales_receipt', 'stock_adjustment']),
            'subject_id' => fake()->numberBetween(1, 100),
            'description' => $descriptions[$action],
            'meta' => null,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
        ];
    }
}
