<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockClearanceAllocation extends Model
{
    protected $fillable = [
        'stock_in_item_id',
        'allocated_quantity',
        'reason',
        'created_by',
    ];

    protected $casts = [
        'allocated_quantity' => 'integer',
    ];

    public function stockInItem(): BelongsTo
    {
        return $this->belongsTo(StockInItem::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
