<?php

namespace App\Support\Alerts;

use App\Models\Alert;
use App\Models\StockInItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AlertGenerator
{
    /**
     * Generate expiry-related alerts (expired_stock, expiry_warning) for a single user.
     *
     * RBAC notes:
     * - Alerts are only generated if the user has at least one of:
     *   - alerts.expired_stock
     *   - alerts.expiry_warning
     * - Branch scoping matches other modules:
     *   - Super admin: all branches
     *   - Branch user: their own branch only
     */
    public static function generateExpiryAlertsForUser(User $user): void
    {
        if (! $user->can('alerts.expired_stock') && ! $user->can('alerts.expiry_warning')) {
            return;
        }

        $today = Carbon::today()->toDateString();

        // Number of days ahead for "expiry_warning" alerts.
        $warningDays = 7;
        $nearDate = Carbon::today()->addDays($warningDays)->toDateString();

        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = (int) ($user->branch_id ?? 0);

        $baseQuery = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_receipts.id', '=', 'stock_in_items.stock_in_receipt_id')
            ->join('products', 'products.id', '=', 'stock_in_items.product_id')
            ->whereNull('stock_in_receipts.voided_at')
            ->whereNotNull('stock_in_items.expiry_date')
            ->where('stock_in_items.remaining_quantity', '>', 0)
            ->where('stock_in_items.expiry_date', '<=', $nearDate)
            ->when(! $isSuperAdmin && $branchId > 0, fn ($q) => $q->where('stock_in_receipts.branch_id', $branchId));

        $rows = $baseQuery
            ->orderBy('stock_in_items.expiry_date')
            ->limit(500)
            ->get([
                'stock_in_items.id as stock_in_item_id',
                'stock_in_items.product_id',
                'stock_in_items.expiry_date',
                'stock_in_items.remaining_quantity',
                'products.name as product_name',
                'stock_in_receipts.branch_id',
            ]);

        foreach ($rows as $row) {
            $expiryDate = Carbon::parse($row->expiry_date)->toDateString();
            $branchIdForRow = (int) ($row->branch_id ?? 0);
            $remainingQty = (int) ($row->remaining_quantity ?? 0);

            $type = $expiryDate <= $today ? 'expired_stock' : 'expiry_warning';

            if ($type === 'expired_stock' && ! $user->can('alerts.expired_stock')) {
                continue;
            }

            if ($type === 'expiry_warning' && ! $user->can('alerts.expiry_warning')) {
                continue;
            }

            $alreadyExists = Alert::query()
                ->forUser((int) $user->id)
                ->byType($type)
                ->where('branch_id', $branchIdForRow)
                ->whereJsonContains('metadata->item_id', (int) $row->stock_in_item_id)
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $title = $type === 'expired_stock'
                ? __('Expired stock: :product', ['product' => $row->product_name])
                : __('Expiry warning: :product', ['product' => $row->product_name]);

            $message = $type === 'expired_stock'
                ? __(':product has :qty units expired on :date', [
                    'product' => $row->product_name,
                    'qty' => $remainingQty,
                    'date' => $expiryDate,
                ])
                : __(':product will expire on :date (:qty units remaining)', [
                    'product' => $row->product_name,
                    'qty' => $remainingQty,
                    'date' => $expiryDate,
                ]);

            Alert::query()->create([
                'user_id' => (int) $user->id,
                'branch_id' => $branchIdForRow > 0 ? $branchIdForRow : null,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'metadata' => [
                    'item_id' => (int) $row->stock_in_item_id,
                    'product_id' => (int) $row->product_id,
                    'branch_id' => $branchIdForRow,
                    'expiry_date' => $expiryDate,
                    'remaining_quantity' => $remainingQty,
                ],
            ]);
        }
    }

    /**
     * Generate low stock alerts for a single user.
     */
    public static function generateLowStockAlertsForUser(User $user): void
    {
        if (! $user->can('alerts.low_stock')) {
            return;
        }

        $isSuperAdmin = $user->isSuperAdmin();
        $branchId = (int) ($user->branch_id ?? 0);

        $lowStockProducts = \App\Models\ProductStock::query()
            ->with(['product', 'branch'])
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->when(! $isSuperAdmin && $branchId > 0, fn ($q) => $q->where('branch_id', $branchId))
            ->limit(100)
            ->get();

        foreach ($lowStockProducts as $stock) {
            $branchIdForRow = (int) ($stock->branch_id ?? 0);
            $type = 'low_stock';

            $alreadyExists = Alert::query()
                ->forUser((int) $user->id)
                ->byType($type)
                ->where('branch_id', $branchIdForRow)
                ->whereJsonContains('metadata->product_id', (int) $stock->product_id)
                ->where('created_at', '>=', Carbon::today())
                ->exists();

            if ($alreadyExists) {
                continue;
            }

            $title = __('Low stock: :product', ['product' => $stock->product->name]);
            $message = __('Product :product is at :qty units (Minimum: :min)', [
                'product' => $stock->product->name,
                'qty' => $stock->current_stock,
                'min' => $stock->minimum_stock,
            ]);

            Alert::query()->create([
                'user_id' => (int) $user->id,
                'branch_id' => $branchIdForRow > 0 ? $branchIdForRow : null,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'metadata' => [
                    'product_id' => (int) $stock->product_id,
                    'branch_id' => $branchIdForRow,
                    'current_stock' => (int) $stock->current_stock,
                    'minimum_stock' => (int) $stock->minimum_stock,
                ],
            ]);
        }
    }
}

