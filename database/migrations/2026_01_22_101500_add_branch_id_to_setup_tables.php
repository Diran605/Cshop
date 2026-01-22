<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $defaultBranchId = DB::table('branches')->orderBy('id')->value('id');

        Schema::table('categories', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->cascadeOnDelete();
            $table->index(['branch_id', 'name']);
        });

        Schema::table('bulk_units', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->cascadeOnDelete();
            $table->index(['branch_id', 'name']);
        });

        Schema::table('bulk_types', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->cascadeOnDelete();
            $table->index(['branch_id', 'name']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->cascadeOnDelete();
            $table->index(['branch_id', 'name']);
        });

        if ($defaultBranchId) {
            DB::table('categories')->whereNull('branch_id')->update(['branch_id' => $defaultBranchId]);
            DB::table('bulk_units')->whereNull('branch_id')->update(['branch_id' => $defaultBranchId]);
            DB::table('bulk_types')->whereNull('branch_id')->update(['branch_id' => $defaultBranchId]);
            DB::table('products')->whereNull('branch_id')->update(['branch_id' => $defaultBranchId]);
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('bulk_types', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('bulk_units', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
