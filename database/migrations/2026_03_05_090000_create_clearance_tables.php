<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clearance discount rules - configurable by days to expiry
        Schema::create('clearance_discount_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->cascadeOnDelete();
            $table->integer('days_to_expiry_min');
            $table->integer('days_to_expiry_max');
            $table->integer('discount_percentage');
            $table->string('status_label', 50);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['branch_id', 'days_to_expiry_min', 'days_to_expiry_max'], 'cdr_branch_days_idx');
        });

        // Clearance items - items flagged for clearance
        Schema::create('clearance_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_in_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('discount_rule_id')->nullable()->constrained('clearance_discount_rules')->nullOnDelete();

            $table->date('expiry_date');
            $table->integer('days_to_expiry');
            $table->enum('status', ['approaching', 'urgent', 'critical', 'expired', 'actioned'])->default('approaching');

            $table->integer('quantity');
            $table->decimal('original_price', 12, 2);
            $table->decimal('suggested_discount_pct', 5, 2)->default(0);
            $table->decimal('clearance_price', 12, 2)->nullable();

            $table->string('action_type', 20)->nullable(); // discount, donate, dispose
            $table->timestamp('actioned_at')->nullable();
            $table->foreignId('actioned_by')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'status'], 'ci_branch_status_idx');
            $table->index(['expiry_date'], 'ci_expiry_idx');
            $table->index(['stock_in_item_id'], 'ci_stock_item_idx');
        });

        // Clearance actions - records of all actions taken
        Schema::create('clearance_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clearance_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('action_type', ['discount', 'donate', 'dispose', 'sold']);
            $table->integer('quantity');
            $table->decimal('original_value', 12, 2);
            $table->decimal('action_value', 12, 2)->nullable();
            $table->decimal('recovered_value', 12, 2)->default(0);
            $table->decimal('loss_value', 12, 2)->default(0);

            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'action_type', 'created_at'], 'ca_branch_type_date_idx');
        });

        // Donations
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clearance_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('organization_name');
            $table->string('organization_contact')->nullable();
            $table->string('organization_address')->nullable();

            $table->integer('total_items');
            $table->decimal('total_value', 12, 2);
            $table->string('receipt_number', 50)->unique();

            $table->text('notes')->nullable();
            $table->timestamp('donated_at');
            $table->timestamps();

            $table->index(['branch_id', 'donated_at'], 'don_branch_date_idx');
        });

        // Donation items
        Schema::create('donation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_in_item_id')->nullable()->constrained()->nullOnDelete();

            $table->integer('quantity');
            $table->decimal('unit_value', 12, 2);
            $table->decimal('total_value', 12, 2);
            $table->date('expiry_date')->nullable();

            $table->timestamps();
        });

        // Disposals
        Schema::create('disposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clearance_item_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->enum('disposal_reason', ['expired', 'damaged', 'quality_issue', 'recall', 'other']);
            $table->text('reason_details')->nullable();

            $table->integer('total_items');
            $table->decimal('total_loss', 12, 2);

            $table->string('disposal_method', 50)->nullable(); // trash, incineration, return_to_supplier
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();

            $table->timestamp('disposed_at');
            $table->timestamps();

            $table->index(['branch_id', 'disposed_at'], 'disp_branch_date_idx');
        });

        // Disposal items
        Schema::create('disposal_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('disposal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stock_in_item_id')->nullable()->constrained()->nullOnDelete();

            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('total_loss', 12, 2);
            $table->date('expiry_date')->nullable();

            $table->timestamps();
        });

        // Clearance sales tracking
        Schema::create('clearance_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('clearance_item_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('original_price', 12, 2);
            $table->decimal('clearance_price', 12, 2);
            $table->decimal('discount_amount', 12, 2);
            $table->integer('quantity');

            $table->timestamps();

            $table->index(['branch_id', 'created_at'], 'cs_branch_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clearance_sales');
        Schema::dropIfExists('disposal_items');
        Schema::dropIfExists('disposals');
        Schema::dropIfExists('donation_items');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('clearance_actions');
        Schema::dropIfExists('clearance_items');
        Schema::dropIfExists('clearance_discount_rules');
    }
};
