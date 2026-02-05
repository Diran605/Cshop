<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->string('batch_ref_no', 100)->nullable()->after('supplier_name');
            $table->index(['product_id', 'batch_ref_no']);
        });
    }

    public function down(): void
    {
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'batch_ref_no']);
            $table->dropColumn('batch_ref_no');
        });
    }
};
