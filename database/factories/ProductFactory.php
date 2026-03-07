<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use App\Models\UnitType;
use App\Models\BulkType;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $products = [
            'Wireless Bluetooth Headphones',
            'USB-C Charging Cable',
            'LED Desk Lamp',
            'Stainless Steel Water Bottle',
            'Organic Green Tea',
            'Whole Grain Cereal',
            'Fresh Orange Juice',
            'Mixed Nuts Pack',
            'Cotton T-Shirt',
            'Running Sneakers',
            'Denim Jeans',
            'Winter Jacket',
            'Garden Hose 50ft',
            'Plant Pot Set',
            'Power Drill Kit',
            'Screwdriver Set',
            'Basketball',
            'Yoga Mat',
            'Notebook A5',
            'Ballpoint Pens 12pk',
            'Childrens Building Blocks',
            'Board Game Classic',
            'Vitamin C Supplements',
            'Hand Sanitizer 500ml',
            'Facial Moisturizer',
            'Hair Shampoo',
            'Car Phone Mount',
            'Tire Pressure Gauge',
            'Coffee Maker',
            'Toaster Oven',
        ];

        return [
            'name' => fake()->randomElement($products),
            'description' => fake()->sentence(),
            'cost_price' => fake()->randomFloat(2, 100, 10000),
            'min_selling_price' => fake()->randomFloat(2, 150, 15000),
            'selling_price' => fake()->randomFloat(2, 200, 20000),
            'bulk_enabled' => fake()->boolean(30),
            'status' => fake()->randomElement(['active', 'active', 'active', 'active', 'inactive']),
        ];
    }
}
