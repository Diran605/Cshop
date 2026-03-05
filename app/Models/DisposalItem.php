<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisposalItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'disposal_id',
        'product_id',
        'stock_in_item_id',
        'quantity',
        'unit_cost',
        'total_loss',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function disposal(): BelongsTo
    {
        return $this->belongsTo(Disposal::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockInItem(): BelongsTo
    {
        return $this->belongsTo(StockInItem::class);
    }
}
