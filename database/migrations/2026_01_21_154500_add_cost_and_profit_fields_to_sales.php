<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_items', function (Blueprint $table) {
            $table->decimal('unit_cost', 12, 2)->nullable()->after('unit_price');
            $table->decimal('line_cost', 12, 2)->nullable()->after('unit_cost');
            $table->decimal('line_profit', 12, 2)->nullable()->after('line_cost');
        });

        Schema::table('sales_receipts', function (Blueprint $table) {
            $table->decimal('cogs_total', 12, 2)->default(0)->after('grand_total');
            $table->decimal('profit_total', 12, 2)->default(0)->after('cogs_total');
        });
    }

    public function down(): void
    {
        Schema::table('sales_items', function (Blueprint $table) {
            $table->dropColumn(['unit_cost', 'line_cost', 'line_profit']);
        });

        Schema::table('sales_receipts', function (Blueprint $table) {
            $table->dropColumn(['cogs_total', 'profit_total']);
        });
    }
};
