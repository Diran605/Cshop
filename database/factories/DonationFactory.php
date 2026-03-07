<?php

namespace Database\Factories;

use App\Models\Donation;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DonationFactory extends Factory
{
    protected $model = Donation::class;

    public function definition(): array
    {
        $organizations = [
            'Hope Community Center',
            'City Food Bank',
            'Childrens Welfare Foundation',
            'Senior Citizens Home',
            'Local Animal Shelter',
            'Homeless Shelter Network',
            'Community Health Clinic',
            'Youth Development Center',
            'Disaster Relief Fund',
            'Educational Charity Trust',
        ];

        $notes = [
            'Regular monthly donation',
            'Emergency relief donation',
            'Holiday season contribution',
            'Corporate social responsibility initiative',
            'Expiry approaching items donated',
        ];

        return [
            'organization_name' => fake()->randomElement($organizations),
            'organization_contact' => fake()->optional()->phoneNumber(),
            'organization_address' => fake()->optional()->streetAddress(),
            'total_items' => fake()->numberBetween(1, 50),
            'total_value' => fake()->randomFloat(2, 1000, 50000),
            'receipt_number' => 'DON-' . fake()->unique()->numerify('######'),
            'notes' => fake()->optional()->randomElement($notes),
            'donated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
