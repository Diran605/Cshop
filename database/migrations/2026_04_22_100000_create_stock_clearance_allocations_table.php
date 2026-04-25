<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_clearance_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_in_item_id')->constrained('stock_in_items')->restrictOnDelete();
            $table->integer('allocated_quantity')->default(0);
            $table->text('reason')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['stock_in_item_id']);
            $table->index(['created_by']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_clearance_allocations');
    }
};
