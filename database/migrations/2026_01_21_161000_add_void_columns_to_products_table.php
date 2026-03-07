<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('void_requested_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->timestamp('void_requested_at')->nullable()->after('void_requested_by');
            $table->text('void_reason')->nullable()->after('void_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['void_requested_by']);
            $table->dropColumn(['void_requested_by', 'void_requested_at', 'void_reason']);
        });
    }
};
