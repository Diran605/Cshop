<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('product_id');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('movement_type', 10);
            $table->integer('quantity');
            $table->integer('before_stock');
            $table->integer('after_stock');

            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();

            $table->foreignId('stock_in_receipt_id')->nullable()->constrained('stock_in_receipts')->nullOnDelete();
            $table->foreignId('sales_receipt_id')->nullable()->constrained('sales_receipts')->nullOnDelete();

            $table->dateTime('moved_at');
            $table->string('notes', 1000)->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'product_id', 'moved_at']);
            $table->index(['movement_type', 'moved_at']);
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
