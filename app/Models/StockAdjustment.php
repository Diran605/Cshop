<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'product_id',
        'adjustment_type',
        'current_stock',
        'adjustment_quantity',
        'target_stock',
        'status',
        'reason',
        'requested_by',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'current_stock' => 'integer',
        'adjustment_quantity' => 'integer',
        'target_stock' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    // Adjustment types
    const TYPE_VOID_PRODUCT = 'void_product';
    const TYPE_OPENING_STOCK = 'opening_stock';
    const TYPE_STOCK_IN = 'stock_in';
    const TYPE_STOCK_IN_VOID = 'stock_in_void';
    const TYPE_SALES_VOID = 'sales_void';
    const TYPE_MANUAL_ADJUSTMENT = 'manual_adjustment';

    // Status values
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }
}
