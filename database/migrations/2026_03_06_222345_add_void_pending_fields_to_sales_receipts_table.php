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
        Schema::table('sales_receipts', function (Blueprint $table) {
            $table->timestamp('void_requested_at')->nullable()->after('voided_at');
            $table->foreignId('void_requested_by')->nullable()->after('void_requested_at')->constrained('users')->nullOnDelete();
            $table->text('void_reason')->nullable()->change();
            $table->foreignId('void_reviewed_by')->nullable()->after('void_reason')->constrained('users')->nullOnDelete();
            $table->timestamp('void_reviewed_at')->nullable()->after('void_reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_receipts', function (Blueprint $table) {
            $table->dropForeign(['void_requested_by']);
            $table->dropForeign(['void_reviewed_by']);
            $table->dropColumn(['void_requested_at', 'void_requested_by', 'void_reviewed_by', 'void_reviewed_at']);
        });
    }
};
