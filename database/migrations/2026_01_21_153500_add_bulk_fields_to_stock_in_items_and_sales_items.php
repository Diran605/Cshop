<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->string('entry_mode', 10)->default('unit')->after('product_id');
            $table->integer('bulk_quantity')->nullable()->after('entry_mode');
            $table->integer('units_per_bulk')->nullable()->after('bulk_quantity');
            $table->unsignedBigInteger('bulk_type_id')->nullable()->after('units_per_bulk');

            $table->index('bulk_type_id');
        });

        Schema::table('sales_items', function (Blueprint $table) {
            $table->string('entry_mode', 10)->default('unit')->after('product_id');
            $table->integer('bulk_quantity')->nullable()->after('entry_mode');
            $table->integer('units_per_bulk')->nullable()->after('bulk_quantity');
            $table->unsignedBigInteger('bulk_type_id')->nullable()->after('units_per_bulk');

            $table->index('bulk_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->dropIndex(['bulk_type_id']);
            $table->dropColumn(['entry_mode', 'bulk_quantity', 'units_per_bulk', 'bulk_type_id']);
        });

        Schema::table('sales_items', function (Blueprint $table) {
            $table->dropIndex(['bulk_type_id']);
            $table->dropColumn(['entry_mode', 'bulk_quantity', 'units_per_bulk', 'bulk_type_id']);
        });
    }
};
