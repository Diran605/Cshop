<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $users = User::where('role', '!=', 'super_admin')->get();

        // Create 20 expenses per branch
        foreach ($branches as $branch) {
            $branchUsers = $users->where('branch_id', $branch->id);

            for ($i = 0; $i < 20; $i++) {
                Expense::factory()->create([
                    'branch_id' => $branch->id,
                    'user_id' => $branchUsers->random()->id,
                ]);
            }
        }
    }
}
