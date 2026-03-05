<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Branch;
use App\Models\ClearanceDiscountRule;
use App\Models\ClearanceItem;
use App\Models\Product;
use App\Models\StockInItem;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ScanExpiryProducts extends Command
{
    protected $signature = 'clearance:scan-expiry {--branch= : Specific branch ID to scan}';

    protected $description = 'Scan for products approaching expiry and create clearance items';

    public function handle(): int
    {
        $this->info('Starting expiry product scan...');

        $branchId = $this->option('branch');
        $branches = $branchId ? Branch::where('id', $branchId)->get() : Branch::all();

        $totalScanned = 0;
        $totalFlagged = 0;
        $totalUpdated = 0;

        foreach ($branches as $branch) {
            $this->info("Scanning branch: {$branch->name}");

            // Get stock items with expiry dates
            $stockItems = DB::table('stock_in_items')
                ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
                ->join('products', 'products.id', '=', 'stock_in_items.product_id')
                ->leftJoin('product_stocks', function ($join) use ($branch) {
                    $join->on('product_stocks.product_id', '=', 'stock_in_items.product_id')
                        ->where('product_stocks.branch_id', $branch->id);
                })
                ->where('stock_in_receipts.branch_id', $branch->id)
                ->whereNull('stock_in_receipts.voided_at')
                ->where('stock_in_items.remaining_quantity', '>', 0)
                ->whereNotNull('stock_in_items.expiry_date')
                ->where('stock_in_items.expiry_date', '<=', Carbon::today()->addDays(30)) // 30 days window
                ->select([
                    'stock_in_items.id as stock_in_item_id',
                    'stock_in_items.product_id',
                    'stock_in_items.expiry_date',
                    'stock_in_items.remaining_quantity',
                    'products.name as product_name',
                    'products.selling_price',
                    'product_stocks.cost_price',
                ])
                ->get();

            $totalScanned += $stockItems->count();

            foreach ($stockItems as $item) {
                $daysToExpiry = (int) Carbon::today()->diffInDays(Carbon::parse($item->expiry_date), false);

                // Check if clearance item already exists
                $existingClearance = ClearanceItem::where('stock_in_item_id', $item->stock_in_item_id)
                    ->where('status', '!=', ClearanceItem::STATUS_ACTIONED)
                    ->first();

                if ($existingClearance) {
                    // Update existing clearance item
                    $existingClearance->days_to_expiry = $daysToExpiry;
                    $existingClearance->updateStatus();
                    $totalUpdated++;
                    continue;
                }

                // Get applicable discount rule
                $discountRule = ClearanceDiscountRule::getApplicableRule($daysToExpiry, $branch->id);

                // Determine status
                $status = ClearanceDiscountRule::determineStatus($daysToExpiry);

                // Only create clearance item if urgent, critical, or expired
                if (!in_array($status, [ClearanceItem::STATUS_URGENT, ClearanceItem::STATUS_CRITICAL, ClearanceItem::STATUS_EXPIRED])) {
                    continue;
                }

                $originalPrice = (float) ($item->selling_price ?? $item->cost_price ?? 0);
                $suggestedDiscount = $discountRule?->discount_percentage ?? 0;

                // Create clearance item
                ClearanceItem::create([
                    'branch_id' => $branch->id,
                    'stock_in_item_id' => $item->stock_in_item_id,
                    'product_id' => $item->product_id,
                    'discount_rule_id' => $discountRule?->id,
                    'expiry_date' => $item->expiry_date,
                    'days_to_expiry' => $daysToExpiry,
                    'status' => $status,
                    'quantity' => $item->remaining_quantity,
                    'original_price' => $originalPrice,
                    'suggested_discount_pct' => $suggestedDiscount,
                ]);

                $totalFlagged++;

                // Create alert for managers
                $this->createAlert($branch, $item, $status, $daysToExpiry);
            }
        }

        $this->info("Scan complete:");
        $this->line("- Items scanned: {$totalScanned}");
        $this->line("- New items flagged: {$totalFlagged}");
        $this->line("- Items updated: {$totalUpdated}");

        return self::SUCCESS;
    }

    protected function createAlert(Branch $branch, $item, string $status, int $daysToExpiry): void
    {
        $statusLabels = [
            ClearanceItem::STATUS_URGENT => 'Urgent',
            ClearanceItem::STATUS_CRITICAL => 'Critical',
            ClearanceItem::STATUS_EXPIRED => 'Expired',
        ];

        $title = "{$statusLabels[$status]}: {$item->product_name}";
        $message = $daysToExpiry < 0
            ? "Product '{$item->product_name}' has expired. {$item->remaining_quantity} units need disposal."
            : "Product '{$item->product_name}' expires in {$daysToExpiry} days. {$item->remaining_quantity} units need clearance action.";

        // Get branch managers to notify
        $managers = $branch->users()
            ->whereHas('roles', fn ($q) => $q->where('name', 'branch_manager'))
            ->orWhere('role', 'branch_admin')
            ->get();

        foreach ($managers as $manager) {
            Alert::create([
                'user_id' => $manager->id,
                'branch_id' => $branch->id,
                'type' => 'clearance_action',
                'title' => $title,
                'message' => $message,
                'metadata' => [
                    'product_id' => $item->product_id,
                    'stock_in_item_id' => $item->stock_in_item_id,
                    'days_to_expiry' => $daysToExpiry,
                    'status' => $status,
                    'quantity' => $item->remaining_quantity,
                ],
            ]);
        }
    }
}
