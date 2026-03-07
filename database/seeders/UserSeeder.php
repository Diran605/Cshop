<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Create super admin
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'super_admin',
        ]);

        // Create branch admins and regular users
        $branches = Branch::all();
        foreach ($branches as $branch) {
            // Branch admin
            User::factory()->create([
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'password' => Hash::make('password'),
                'role' => 'branch_admin',
                'branch_id' => $branch->id,
            ]);

            // Regular users (5 per branch)
            User::factory()->count(5)->create([
                'role' => 'user',
                'branch_id' => $branch->id,
            ]);
        }
    }
}
