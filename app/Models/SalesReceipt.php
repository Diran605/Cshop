<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_no',
        'branch_id',
        'user_id',
        'sold_at',
        'payment_method',
        'sub_total',
        'discount_total',
        'grand_total',
        'amount_paid',
        'change_due',
        'notes',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesItem::class);
    }
}
