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
        'entry_mode',
        'bulk_quantity',
        'units_per_bulk',
        'bulk_type_id',
        'quantity',
        'cost_price',
        'line_total',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(StockInReceipt::class, 'stock_in_receipt_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
