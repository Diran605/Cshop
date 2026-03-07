<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Core data (no dependencies)
        $this->call([
            BranchSeeder::class,
            CategorySeeder::class,
            UnitTypeSeeder::class,
            BulkUnitSeeder::class,
            BulkTypeSeeder::class,
        ]);

        // Users (depends on Branch)
        $this->call([
            UserSeeder::class,
        ]);

        // Products and stock (depends on Branch, Category, UnitType, BulkType, User)
        $this->call([
            ProductSeeder::class,
        ]);

        // Stock in receipts (depends on Branch, User, Product)
        $this->call([
            StockInSeeder::class,
        ]);

        // Sales receipts (depends on Branch, User, Product)
        $this->call([
            SalesSeeder::class,
        ]);

        // Stock adjustments (depends on Branch, User, Product)
        $this->call([
            StockAdjustmentSeeder::class,
        ]);

        // Activity logs (depends on Branch, User)
        $this->call([
            ActivityLogSeeder::class,
        ]);

        // Expenses (depends on Branch, User)
        $this->call([
            ExpenseSeeder::class,
        ]);

        // Alerts (depends on Branch)
        $this->call([
            AlertSeeder::class,
        ]);

        // Clearance actions (depends on Branch, Product, User)
        $this->call([
            ClearanceSeeder::class,
        ]);

        // Disposals (depends on Branch, Product, User)
        $this->call([
            DisposalSeeder::class,
        ]);

        // Donations (depends on Branch, Product, User)
        $this->call([
            DonationSeeder::class,
        ]);
    }
}
