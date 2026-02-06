<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function dropUniqueIndexByColumnsIfExists(string $table, array $columns): void
    {
        $columnsCsv = implode(',', $columns);

        $rows = DB::select(
            "SELECT INDEX_NAME as index_name, GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) as cols\n".
            "FROM information_schema.STATISTICS\n".
            "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND NON_UNIQUE = 0\n".
            "GROUP BY INDEX_NAME",
            [$table]
        );

        foreach ($rows as $row) {
            $idx = (string) ($row->index_name ?? '');
            $cols = (string) ($row->cols ?? '');
            if ($idx !== 'PRIMARY' && $cols === $columnsCsv) {
                DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$idx}`");
            }
        }
    }

    private function uniqueIndexByColumnsExists(string $table, array $columns): bool
    {
        $columnsCsv = implode(',', $columns);

        $rows = DB::select(
            "SELECT GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) as cols\n".
            "FROM information_schema.STATISTICS\n".
            "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND NON_UNIQUE = 0\n".
            "GROUP BY INDEX_NAME",
            [$table]
        );

        foreach ($rows as $row) {
            $cols = (string) ($row->cols ?? '');
            if ($cols === $columnsCsv) {
                return true;
            }
        }

        return false;
    }

    public function up(): void
    {
        if (! Schema::hasTable('roles')) {
            return;
        }

        if (! Schema::hasColumn('roles', 'branch_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->nullable()->after('id');
                $table->index('branch_id', 'roles_team_foreign_key_index');
            });
        }

        if (Schema::hasTable('model_has_roles') && ! Schema::hasColumn('model_has_roles', 'branch_id')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->default(0)->after('role_id');
                $table->index('branch_id', 'model_has_roles_team_foreign_key_index');
            });
        }

        if (Schema::hasTable('model_has_permissions') && ! Schema::hasColumn('model_has_permissions', 'branch_id')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('branch_id')->default(0)->after('permission_id');
                $table->index('branch_id', 'model_has_permissions_team_foreign_key_index');
            });
        }

        if (Schema::hasTable('roles')) {
            $this->dropUniqueIndexByColumnsIfExists('roles', ['name', 'guard_name']);

            if (! $this->uniqueIndexByColumnsExists('roles', ['branch_id', 'name', 'guard_name'])) {
                Schema::table('roles', function (Blueprint $table) {
                    $table->unique(['branch_id', 'name', 'guard_name']);
                });
            }
        }

        if (Schema::hasTable('model_has_roles')) {
            DB::table('model_has_roles')->whereNull('branch_id')->update(['branch_id' => 0]);

            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
                $table->dropPrimary();
                $table->primary(['branch_id', 'role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->onDelete('cascade');
            });
        }

        if (Schema::hasTable('model_has_permissions')) {
            DB::table('model_has_permissions')->whereNull('branch_id')->update(['branch_id' => 0]);

            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->dropForeign(['permission_id']);
                $table->dropPrimary();
                $table->primary(['branch_id', 'permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
                $table->foreign('permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('model_has_permissions')) {
            Schema::table('model_has_permissions', function (Blueprint $table) {
                $table->dropForeign(['permission_id']);
                $table->dropPrimary();
                $table->primary(['permission_id', 'model_id', 'model_type'], 'model_has_permissions_permission_model_type_primary');
                $table->foreign('permission_id')
                    ->references('id')
                    ->on('permissions')
                    ->onDelete('cascade');

                if (Schema::hasColumn('model_has_permissions', 'branch_id')) {
                    $table->dropIndex('model_has_permissions_team_foreign_key_index');
                    $table->dropColumn('branch_id');
                }
            });
        }

        if (Schema::hasTable('model_has_roles')) {
            Schema::table('model_has_roles', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
                $table->dropPrimary();
                $table->primary(['role_id', 'model_id', 'model_type'], 'model_has_roles_role_model_type_primary');
                $table->foreign('role_id')
                    ->references('id')
                    ->on('roles')
                    ->onDelete('cascade');

                if (Schema::hasColumn('model_has_roles', 'branch_id')) {
                    $table->dropIndex('model_has_roles_team_foreign_key_index');
                    $table->dropColumn('branch_id');
                }
            });
        }

        if (Schema::hasTable('roles')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->dropUnique(['branch_id', 'name', 'guard_name']);
                $table->unique(['name', 'guard_name']);

                if (Schema::hasColumn('roles', 'branch_id')) {
                    $table->dropIndex('roles_team_foreign_key_index');
                    $table->dropColumn('branch_id');
                }
            });
        }
    }
};
