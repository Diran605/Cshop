<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\ExpenseType;
use Illuminate\Database\Seeder;

class ExpenseTypeSeeder extends Seeder
{
    public function run(): void
    {
        $defaultTypes = [
            ['name' => 'Rent', 'description' => 'Shop or office rental fees'],
            ['name' => 'Utilities', 'description' => 'Electricity, water, and other utilities'],
            ['name' => 'Salaries', 'description' => 'Employee wages and salaries'],
            ['name' => 'Supplies', 'description' => 'Office and operational supplies'],
            ['name' => 'Marketing', 'description' => 'Advertising and promotional expenses'],
            ['name' => 'Transportation', 'description' => 'Delivery and transport costs'],
            ['name' => 'Maintenance', 'description' => 'Equipment and facility repairs'],
            ['name' => 'Insurance', 'description' => 'Business insurance premiums'],
            ['name' => 'Taxes', 'description' => 'Business taxes and levies'],
            ['name' => 'Miscellaneous', 'description' => 'Other business expenses'],
        ];

        $branches = Branch::query()->pluck('id');

        foreach ($branches as $branchId) {
            foreach ($defaultTypes as $type) {
                ExpenseType::query()->create([
                    'branch_id' => $branchId,
                    'name' => $type['name'],
                    'description' => $type['description'],
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Expense types seeded for ' . $branches->count() . ' branches.');
    }
}
