<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, we need to modify the enum to include 'declined' and 'reversed'
        Schema::table('clearance_items', function (Blueprint $table) {
            $table->enum('approval_status', [
                'manual',
                'auto_suggested',
                'pending_approval',
                'approved',
                'declined',
                'rejected',
                'reversed'
            ])->default('manual')->change();
        });
    }

    public function down(): void
    {
        Schema::table('clearance_items', function (Blueprint $table) {
            $table->enum('approval_status', [
                'manual',
                'auto_suggested',
                'pending_approval',
                'approved',
                'rejected'
            ])->default('manual')->change();
        });
    }
};
