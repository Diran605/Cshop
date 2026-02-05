<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sales_receipt_id',
        'product_id',
        'entry_mode',
        'bulk_quantity',
        'units_per_bulk',
        'bulk_type_id',
        'quantity',
        'unit_price',
        'unit_cost',
        'line_total',
        'line_cost',
        'line_profit',
        'is_low_profit',
        'is_loss',
    ];

    public function receipt(): BelongsTo
    {
        return $this->belongsTo(SalesReceipt::class, 'sales_receipt_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
