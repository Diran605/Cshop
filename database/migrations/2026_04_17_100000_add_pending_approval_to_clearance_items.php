<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clearance_items', function (Blueprint $table) {
            // Add approval_status: 'auto_suggested', 'pending_approval', 'approved', 'rejected', 'manual'
            $table->enum('approval_status', ['manual', 'auto_suggested', 'pending_approval', 'approved', 'rejected'])
                ->default('manual')
                ->after('status');

            // Add suggested_at timestamp to track when item was auto-suggested
            $table->timestamp('suggested_at')->nullable()->after('approval_status');

            // Add suggested_by user for audit trail
            $table->foreignId('suggested_by')->nullable()->constrained('users')->nullOnDelete()->after('suggested_at');

            // Add approval_notes for manager feedback
            $table->text('approval_notes')->nullable()->after('suggested_by');

            // Create index for filtering pending approvals
            $table->index(['approval_status', 'branch_id', 'created_at'], 'ci_approval_status_idx');
        });
    }

    public function down(): void
    {
        Schema::table('clearance_items', function (Blueprint $table) {
            $table->dropIndex('ci_approval_status_idx');
            $table->dropForeignIdFor(\App\Models\User::class, 'suggested_by');
            $table->dropColumn([
                'approval_status',
                'suggested_at',
                'suggested_by',
                'approval_notes',
            ]);
        });
    }
};
