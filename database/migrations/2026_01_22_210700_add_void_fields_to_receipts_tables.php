<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_receipts', function (Blueprint $table) {
            $table->timestamp('voided_at')->nullable()->after('notes');
            $table->foreignId('voided_by')->nullable()->after('voided_at')->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable()->after('voided_by');

            $table->index(['branch_id', 'voided_at']);
        });

        Schema::table('stock_in_receipts', function (Blueprint $table) {
            $table->timestamp('voided_at')->nullable()->after('total_cost');
            $table->foreignId('voided_by')->nullable()->after('voided_at')->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable()->after('voided_by');

            $table->index(['branch_id', 'voided_at']);
        });
    }

    public function down(): void
    {
        Schema::table('sales_receipts', function (Blueprint $table) {
            $table->dropIndex(['branch_id', 'voided_at']);
            $table->dropConstrainedForeignId('voided_by');
            $table->dropColumn(['voided_at', 'void_reason']);
        });

        Schema::table('stock_in_receipts', function (Blueprint $table) {
            $table->dropIndex(['branch_id', 'voided_at']);
            $table->dropConstrainedForeignId('voided_by');
            $table->dropColumn(['voided_at', 'void_reason']);
        });
    }
};
