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

            'sales.view',
            'sales.post',
            'sales.edit',
            'sales.delete',

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
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
