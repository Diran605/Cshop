<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->string('supplier_name')->nullable()->after('product_id');
            $table->index(['product_id', 'supplier_name']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'supplier_name']);
            $table->dropColumn('supplier_name');
        });
    }
};
