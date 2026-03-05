<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Disposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'clearance_item_id',
        'user_id',
        'disposal_reason',
        'reason_details',
        'total_items',
        'total_loss',
        'disposal_method',
        'notes',
        'photo_path',
        'disposed_at',
    ];

    protected $casts = [
        'disposed_at' => 'datetime',
    ];

    const REASON_EXPIRED = 'expired';
    const REASON_DAMAGED = 'damaged';
    const REASON_QUALITY_ISSUE = 'quality_issue';
    const REASON_RECALL = 'recall';
    const REASON_OTHER = 'other';

    const METHOD_TRASH = 'trash';
    const METHOD_INCINERATION = 'incineration';
    const METHOD_RETURN_TO_SUPPLIER = 'return_to_supplier';

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
        return $this->hasMany(DisposalItem::class);
    }

    /**
     * Get disposal reasons list
     */
    public static function getReasons(): array
    {
        return [
            self::REASON_EXPIRED => 'Product Expired',
            self::REASON_DAMAGED => 'Damaged',
            self::REASON_QUALITY_ISSUE => 'Quality Issue',
            self::REASON_RECALL => 'Product Recall',
            self::REASON_OTHER => 'Other',
        ];
    }

    /**
     * Get disposal methods list
     */
    public static function getMethods(): array
    {
        return [
            self::METHOD_TRASH => 'Trash/General Waste',
            self::METHOD_INCINERATION => 'Incineration',
            self::METHOD_RETURN_TO_SUPPLIER => 'Return to Supplier',
        ];
    }
}
