<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClearanceSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'sales_item_id',
        'clearance_item_id',
        'original_price',
        'clearance_price',
        'discount_amount',
        'quantity',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function salesItem(): BelongsTo
    {
        return $this->belongsTo(SalesItem::class);
    }

    public function clearanceItem(): BelongsTo
    {
        return $this->belongsTo(ClearanceItem::class);
    }

    /**
     * Get savings amount
     */
    public function getSavingsAttribute(): float
    {
        return $this->discount_amount * $this->quantity;
    }
}
