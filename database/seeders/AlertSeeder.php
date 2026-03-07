<?php

namespace Database\Seeders;

use App\Models\Alert;
use App\Models\Branch;
use Illuminate\Database\Seeder;

class AlertSeeder extends Seeder
{
    public function run(): void
    {
        $branches = Branch::all();
        $users = \App\Models\User::all();

        // Create 100 alerts per branch
        foreach ($branches as $branch) {
            $branchUsers = $users->where('branch_id', $branch->id);

            for ($i = 0; $i < 100; $i++) {
                Alert::factory()->create([
                    'branch_id' => $branch->id,
                    'user_id' => $branchUsers->isNotEmpty() ? $branchUsers->random()->id : $users->first()->id,
                ]);
            }
        }
    }
}
