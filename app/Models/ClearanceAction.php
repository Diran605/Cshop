<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClearanceAction extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'clearance_item_id',
        'user_id',
        'action_type',
        'quantity',
        'original_value',
        'action_value',
        'recovered_value',
        'loss_value',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    const ACTION_DISCOUNT = 'discount';
    const ACTION_DONATE = 'donate';
    const ACTION_DISPOSE = 'dispose';
    const ACTION_SOLD = 'sold';

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

    /**
     * Get recovery rate percentage
     */
    public function getRecoveryRateAttribute(): float
    {
        if ($this->original_value <= 0) {
            return 0;
        }
        return round(($this->recovered_value / $this->original_value) * 100, 1);
    }
}
