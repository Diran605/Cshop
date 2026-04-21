<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\ClearanceItem;
use App\Models\StockInItem;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SuggestExpiredItemsForClearance extends Command
{
    protected $signature = 'clearance:suggest-expired-items
                            {--branch_id= : Specific branch ID, or all branches if omitted}
                            {--dry-run : Show what would be created without inserting}';

    protected $description = 'Auto-suggest expired/near-expiry items for clearance manager review';

    public function handle(): int
    {
        $this->info('🔍 Scanning for items to suggest to clearance...');

        $branchId = $this->option('branch_id');
        $isDryRun = $this->option('dry-run');

        $today = Carbon::today()->toDateString();
        $warningDate = Carbon::today()->addDays(7)->toDateString(); // Next 7 days

        // Get branches to process
        $branches = $branchId
            ? Branch::where('id', $branchId)->get()
            : Branch::where('is_active', true)->get();

        $totalCreated = 0;
        $totalSkipped = 0;

        foreach ($branches as $branch) {
            $this->line("\n📍 Processing branch: {$branch->name}");

            // Find stock_in_items that:
            // 1. Will expire within 7 days (or already expired)
            // 2. Have remaining_quantity > 0
            // 3. Are not already in clearance_items (not auto-suggested yet)
            $expiringSoonItems = StockInItem::query()
                ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
                ->join('products', 'products.id', '=', 'stock_in_items.product_id')
                ->where('stock_in_receipts.branch_id', $branch->id)
                ->whereNull('stock_in_receipts.voided_at')
                ->whereNotNull('stock_in_items.expiry_date')
                ->where('stock_in_items.remaining_quantity', '>', 0)
                ->where('stock_in_items.expiry_date', '<=', $warningDate)
                ->leftJoin('clearance_items', function ($join) {
                    $join->on('clearance_items.stock_in_item_id', '=', 'stock_in_items.id')
                        ->where('clearance_items.approval_status', '!=', 'rejected');
                })
                ->whereNull('clearance_items.id') // Not already suggested or approved
                ->select([
                    'stock_in_items.id as stock_in_item_id',
                    'stock_in_items.product_id',
                    'stock_in_items.expiry_date',
                    'stock_in_items.remaining_quantity',
                    'stock_in_items.cost_price',
                    'products.name as product_name',
                    'products.selling_price',
                    'stock_in_receipts.branch_id',
                ])
                ->get();

            if ($expiringSoonItems->isEmpty()) {
                $this->line('   ✓ No items to suggest');
                continue;
            }

            foreach ($expiringSoonItems as $item) {
                $daysToExpiry = (int) Carbon::today()->diffInDays(
                    Carbon::parse($item->expiry_date),
                    false
                );

                // Determine initial status
                $status = $daysToExpiry < 0 ? 'expired' : (
                    $daysToExpiry <= 3 ? 'critical' : (
                        $daysToExpiry <= 7 ? 'urgent' : 'approaching'
                    )
                );

                // Get applicable discount rule
                $suggestedDiscount = 0;
                $discountRuleId = null;
                if ($daysToExpiry >= 0) {
                    // Only suggest discount for items not yet expired
                    $discountRule = DB::table('clearance_discount_rules')
                        ->where('is_active', true)
                        ->where('days_to_expiry_min', '<=', $daysToExpiry)
                        ->where('days_to_expiry_max', '>=', $daysToExpiry)
                        ->where(function ($q) use ($item) {
                            $q->where('branch_id', null)
                                ->orWhere('branch_id', $item->branch_id);
                        })
                        ->orderByDesc('discount_percentage')
                        ->first(['id', 'discount_percentage']);

                    if ($discountRule) {
                        $suggestedDiscount = $discountRule->discount_percentage;
                        $discountRuleId = $discountRule->id;
                    }
                }

                // Create clearance item suggestion
                $clearanceData = [
                    'branch_id' => $item->branch_id,
                    'stock_in_item_id' => $item->stock_in_item_id,
                    'product_id' => $item->product_id,
                    'discount_rule_id' => $discountRuleId,
                    'expiry_date' => $item->expiry_date,
                    'days_to_expiry' => $daysToExpiry,
                    'status' => $status,
                    'quantity' => $item->remaining_quantity,
                    'original_price' => $item->selling_price,
                    'suggested_discount_pct' => $suggestedDiscount,
                    'approval_status' => 'auto_suggested',
                    'suggested_at' => now(),
                ];

                if (!$isDryRun) {
                    ClearanceItem::create($clearanceData);

                    // Log the suggestion
                    ActivityLogger::log(
                        'clearance.auto_suggested',
                        null,
                        "Auto-suggested {$item->product_name} for clearance (expires {$item->expiry_date})",
                        [
                            'product_id' => $item->product_id,
                            'stock_in_item_id' => $item->stock_in_item_id,
                            'days_to_expiry' => $daysToExpiry,
                            'status' => $status,
                        ],
                        $item->branch_id
                    );
                }

                $totalCreated++;
                $this->line("   ✓ Suggested: {$item->product_name} ({$item->remaining_quantity} units, expires {$item->expiry_date})");
            }
        }

        if ($isDryRun) {
            $this->warn("\n⚠️  DRY RUN: {$totalCreated} items WOULD be suggested (not actually created)");
        } else {
            $this->info("\n✅ Successfully suggested {$totalCreated} items for clearance manager review");
            $this->info("   Managers can now review & approve/reject in the ClearanceManager");
        }

        return 0;
    }
}
