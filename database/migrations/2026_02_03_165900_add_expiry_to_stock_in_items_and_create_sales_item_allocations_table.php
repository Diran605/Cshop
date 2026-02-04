<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->date('expiry_date')->nullable()->after('bulk_type_id');
            $table->integer('remaining_quantity')->default(0)->after('quantity');

            $table->index(['product_id', 'expiry_date']);
            $table->index(['product_id', 'remaining_quantity']);
        });

        Schema::create('sales_item_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_item_id')->constrained('sales_items')->cascadeOnDelete();
            $table->foreignId('stock_in_item_id')->constrained('stock_in_items')->restrictOnDelete();
            $table->integer('quantity');
            $table->timestamps();

            $table->index(['stock_in_item_id']);
            $table->index(['sales_item_id']);
        });

        DB::table('stock_in_items')->update(['remaining_quantity' => 0]);

        $productStocks = DB::table('product_stocks')
            ->select(['branch_id', 'product_id', 'current_stock'])
            ->get();

        foreach ($productStocks as $ps) {
            $remaining = (int) ($ps->current_stock ?? 0);
            if ($remaining <= 0) {
                continue;
            }

            $items = DB::table('stock_in_items')
                ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
                ->whereNull('stock_in_receipts.voided_at')
                ->where('stock_in_receipts.branch_id', (int) $ps->branch_id)
                ->where('stock_in_items.product_id', (int) $ps->product_id)
                ->orderByDesc('stock_in_receipts.received_at')
                ->orderByDesc('stock_in_items.id')
                ->select(['stock_in_items.id', 'stock_in_items.quantity'])
                ->get();

            foreach ($items as $item) {
                if ($remaining <= 0) {
                    break;
                }

                $qty = (int) ($item->quantity ?? 0);
                if ($qty <= 0) {
                    continue;
                }

                $assign = min($remaining, $qty);
                DB::table('stock_in_items')->where('id', (int) $item->id)->update([
                    'remaining_quantity' => $assign,
                ]);

                $remaining -= $assign;
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_item_allocations');

        Schema::table('stock_in_items', function (Blueprint $table) {
            $table->dropIndex(['product_id', 'expiry_date']);
            $table->dropIndex(['product_id', 'remaining_quantity']);
            $table->dropColumn(['expiry_date', 'remaining_quantity']);
        });
    }
};
