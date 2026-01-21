<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bulk_types', function (Blueprint $table) {
            $table->foreign('bulk_unit_id', 'bulk_types_bulk_unit_id_fk')
                ->references('id')->on('bulk_units')
                ->cascadeOnDelete();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreign('category_id', 'products_category_id_fk')
                ->references('id')->on('categories')
                ->nullOnDelete();

            $table->foreign('bulk_type_id', 'products_bulk_type_id_fk')
                ->references('id')->on('bulk_types')
                ->nullOnDelete();
        });

        Schema::table('product_stocks', function (Blueprint $table) {
            $table->foreign('product_id', 'product_stocks_product_id_fk')
                ->references('id')->on('products')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_stocks', function (Blueprint $table) {
            $table->dropForeign('product_stocks_product_id_fk');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign('products_category_id_fk');
            $table->dropForeign('products_bulk_type_id_fk');
        });

        Schema::table('bulk_types', function (Blueprint $table) {
            $table->dropForeign('bulk_types_bulk_unit_id_fk');
        });
    }
};
