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
        'customer_name',
        'sub_total',
        'discount_total',
        'grand_total',
        'cogs_total',
        'profit_total',
        'amount_paid',
        'change_due',
        'notes',
        'voided_at',
        'voided_by',
        'void_reason',
        'void_requested_at',
        'void_requested_by',
        'void_reviewed_by',
        'void_reviewed_at',
    ];

    protected $casts = [
        'sold_at' => 'datetime',
        'voided_at' => 'datetime',
        'void_requested_at' => 'datetime',
        'void_reviewed_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function voidRequestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'void_requested_by');
    }

    public function voidReviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'void_reviewed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesItem::class);
    }

    public function isVoidPending(): bool
    {
        return $this->void_requested_at !== null && $this->voided_at === null;
    }

    public function isVoided(): bool
    {
        return $this->voided_at !== null;
    }

    public function isActive(): bool
    {
        return $this->void_requested_at === null && $this->voided_at === null;
    }
}
