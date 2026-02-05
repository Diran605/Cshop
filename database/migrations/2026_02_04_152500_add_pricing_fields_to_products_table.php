<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->nullable()->after('category_id');
            $table->decimal('min_selling_price', 12, 2)->nullable()->after('cost_price');
            $table->index(['branch_id', 'min_selling_price']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['branch_id', 'min_selling_price']);
            $table->dropColumn(['cost_price', 'min_selling_price']);
        });
    }
};
