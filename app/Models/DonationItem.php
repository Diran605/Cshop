<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DonationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'donation_id',
        'product_id',
        'stock_in_item_id',
        'quantity',
        'unit_value',
        'total_value',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
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
