<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RbacSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Aggregate permissions for sidebar navigation
            'branches.manage',
            'users.manage',
            'rbac.manage',
            'setup.categories.manage',
            'setup.unit_types.manage',
            'setup.bulk.manage',
            'products.manage',
            'stock_in.manage',
            'stock_levels.view',
            'opening_stock.manage',
            'expenses.manage',
            'reports.view',

            // Granular permissions
            'branches.view',
            'branches.create',
            'branches.edit',
            'branches.delete',

            'users.view',
            'users.create',
            'users.edit',
            'users.delete',

            'rbac.roles.view',
            'rbac.roles.create',
            'rbac.roles.edit',
            'rbac.roles.delete',
            'rbac.permissions.view',
            'rbac.user_roles.assign',

            'setup.categories.view',
            'setup.categories.create',
            'setup.categories.edit',
            'setup.categories.delete',

            'setup.unit_types.view',
            'setup.unit_types.create',
            'setup.unit_types.edit',
            'setup.unit_types.delete',

            'setup.bulk.view',
            'setup.bulk.create',
            'setup.bulk.edit',
            'setup.bulk.delete',

            'products.view',
            'products.create',
            'products.edit',
            'products.delete',

            'stock_in.view',
            'stock_in.post',
            'stock_in.edit',
            'stock_in.delete',

            'stock_levels.view',
            'opening_stock.view',
            'opening_stock.edit',

            'sales.view',
            'sales.post',

            'sales_records.view',
            'sales_records.edit',
            'sales_records.void',
            'sales_records.print',
            'sales_records.batch_print',

            'daily_summary.view',

            'expenses.view',
            'expenses.create',
            'expenses.edit',
            'expenses.delete',

            'reports.sales',
            'reports.profit',
            'reports.stock',
            'reports.expenses',
            'reports.expiry',

            'audit.stock_movements.view',
            'audit.activity_logs.view',

            'alerts.stock_adjustment',
            'alerts.expired_stock',
            'alerts.expiry_warning',
            'alerts.low_stock',

            'clearance.view',
            'clearance.discount',
            'clearance.donate',
            'clearance.dispose',
            'clearance.rules.view',
            'clearance.rules.create',
            'clearance.rules.edit',
            'clearance.rules.delete',
            'clearance.reports',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
