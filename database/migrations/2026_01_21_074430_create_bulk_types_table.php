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
        if (Schema::hasTable('bulk_types')) {
            return;
        }

        Schema::create('bulk_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('bulk_unit_id');
            $table->unsignedInteger('units_per_bulk');
            $table->text('description')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('bulk_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulk_types');
    }
};
