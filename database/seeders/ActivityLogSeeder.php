<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $users = User::all();

        // Create 20 activity logs per branch
        foreach ($branches as $branch) {
            $branchUsers = $users->where('branch_id', $branch->id);

            for ($i = 0; $i < 20; $i++) {
                ActivityLog::factory()->create([
                    'branch_id' => $branch->id,
                    'user_id' => $branchUsers->random()->id,
                ]);
            }
        }
    }
}
