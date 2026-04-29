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
        Schema::table('clearance_actions', function (Blueprint $blueprint) {
            $blueprint->string('status')->default('active')->after('action_type');
            $blueprint->string('reversal_reason')->nullable()->after('notes');
            $blueprint->timestamp('reversed_at')->nullable()->after('reversal_reason');
            $blueprint->unsignedBigInteger('reversed_by')->nullable()->after('reversed_at');
            
            $blueprint->foreign('reversed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clearance_actions', function (Blueprint $blueprint) {
            $blueprint->dropForeign(['reversed_by']);
            $blueprint->dropColumn(['status', 'reversal_reason', 'reversed_at', 'reversed_by']);
        });
    }
};
