<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Branch;
use App\Models\Product;

class ProductStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'product_id',
        'current_stock',
        'minimum_stock',
        'cost_price',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Sync this stock record with actual batches in stock_in_items
     */
    public function syncWithBatches(): void
    {
        $batchData = StockInItem::query()
            ->join('stock_in_receipts', 'stock_in_items.stock_in_receipt_id', '=', 'stock_in_receipts.id')
            ->where('stock_in_items.product_id', $this->product_id)
            ->where('stock_in_receipts.branch_id', $this->branch_id)
            ->whereNull('stock_in_receipts.voided_at')
            ->where('stock_in_items.remaining_quantity', '>', 0)
            ->selectRaw('SUM(remaining_quantity) as total_qty, SUM(remaining_quantity * COALESCE(cost_price, 0)) as total_value')
            ->first();

        $totalQty = (int) ($batchData->total_qty ?? 0);
        $totalValue = (float) ($batchData->total_value ?? 0);
        
        $this->current_stock = $totalQty;
        if ($totalQty > 0) {
            $this->cost_price = $totalValue / $totalQty;
        }
        $this->save();
    }

    /**
     * Sync all stock records for a specific branch or all branches
     */
    public static function syncAll(?int $branchId = null): void
    {
        $stocks = self::query()
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->get();

        foreach ($stocks as $stock) {
            $stock->syncWithBatches();
        }
    }
}
