<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_items', function (Blueprint $table) {
            $table->boolean('is_low_profit')->default(false)->after('line_profit');
            $table->boolean('is_loss')->default(false)->after('is_low_profit');
            $table->index(['is_low_profit']);
            $table->index(['is_loss']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_items', function (Blueprint $table) {
            $table->dropIndex(['is_low_profit']);
            $table->dropIndex(['is_loss']);
            $table->dropColumn(['is_low_profit', 'is_loss']);
        });
    }
};
