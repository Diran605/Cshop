<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockInItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_in_receipt_id',
        'product_id',
        'supplier_name',
        'batch_ref_no',
        'entry_mode',
        'bulk_quantity',
        'units_per_bulk',
        'bulk_type_id',
        'expiry_date',
        'quantity',
        'remaining_quantity',
        'cost_price',
        'line_total',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    protected static function booted()
    {
        static::saved(function ($item) {
            $item->syncProductStock();
        });

        static::deleted(function ($item) {
            $item->syncProductStock();
        });
    }

    public function syncProductStock()
    {
        $branchId = $this->receipt?->branch_id;
        if ($branchId) {
            $stock = ProductStock::firstOrCreate(
                ['branch_id' => $branchId, 'product_id' => $this->product_id],
                ['current_stock' => 0, 'minimum_stock' => 0]
            );
            $stock->syncWithBatches();
        }
    }

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(StockInReceipt::class, 'stock_in_receipt_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function clearanceAllocations()
    {
        return $this->hasMany(StockClearanceAllocation::class);
    }
}
