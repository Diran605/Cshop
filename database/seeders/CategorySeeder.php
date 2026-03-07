<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $branches = \App\Models\Branch::all();

        $categories = [
            'Electronics',
            'Food & Beverages',
            'Clothing',
            'Home & Garden',
            'Sports',
            'Books',
            'Toys',
            'Health',
            'Beauty',
            'Automotive',
        ];

        // Create categories for each branch
        foreach ($branches as $branch) {
            foreach ($categories as $category) {
                \App\Models\Category::create([
                    'name' => $category,
                    'branch_id' => $branch->id,
                ]);
            }
        }
    }
}
