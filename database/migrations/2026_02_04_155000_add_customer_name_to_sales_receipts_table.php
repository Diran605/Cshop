<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_receipts', function (Blueprint $table) {
            $table->string('customer_name')->nullable()->after('payment_method');
            $table->index(['branch_id', 'customer_name']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_receipts', function (Blueprint $table) {
            $table->dropIndex(['branch_id', 'customer_name']);
            $table->dropColumn('customer_name');
        });
    }
};
