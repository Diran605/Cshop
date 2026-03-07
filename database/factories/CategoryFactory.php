<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $categories = [
            'Electronics',
            'Food & Beverages',
            'Clothing & Apparel',
            'Home & Garden',
            'Sports & Outdoors',
            'Books & Stationery',
            'Toys & Games',
            'Health & Wellness',
            'Beauty & Personal Care',
            'Automotive',
            'Kitchen & Dining',
            'Pet Supplies',
            'Office Supplies',
            'Jewelry & Accessories',
            'Furniture',
        ];

        return [
            'name' => fake()->randomElement($categories),
            'description' => fake()->optional()->sentence(),
            'branch_id' => null,
        ];
    }
}
