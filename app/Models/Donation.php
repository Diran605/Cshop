<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Donation extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'clearance_item_id',
        'user_id',
        'organization_name',
        'organization_contact',
        'organization_address',
        'total_items',
        'total_value',
        'receipt_number',
        'notes',
        'donated_at',
    ];

    protected $casts = [
        'donated_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function clearanceItem(): BelongsTo
    {
        return $this->belongsTo(ClearanceItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(DonationItem::class);
    }

    /**
     * Generate next receipt number
     */
    public static function generateReceiptNumber(): string
    {
        $lastId = static::max('id') ?? 0;
        return 'DON-' . str_pad((string) ($lastId + 1), 6, '0', STR_PAD_LEFT);
    }
}
