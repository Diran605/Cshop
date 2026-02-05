<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->string('action', 80);
            $table->string('subject_type', 120)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            $table->string('description', 255)->nullable();
            $table->json('meta')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->timestamps();

            $table->index(['action', 'created_at']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['branch_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
