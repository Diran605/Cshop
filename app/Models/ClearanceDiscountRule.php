<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClearanceDiscountRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'days_to_expiry_min',
        'days_to_expiry_max',
        'discount_percentage',
        'status_label',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function clearanceItems(): HasMany
    {
        return $this->hasMany(ClearanceItem::class, 'discount_rule_id');
    }

    /**
     * Get applicable discount rule for a given days to expiry
     */
    public static function getApplicableRule(int $daysToExpiry, ?int $branchId = null): ?self
    {
        return static::query()
            ->where('is_active', true)
            ->where('days_to_expiry_min', '<=', $daysToExpiry)
            ->where('days_to_expiry_max', '>=', $daysToExpiry)
            ->when($branchId, fn ($q) => $q->where('branch_id', $branchId))
            ->orderByDesc('discount_percentage')
            ->first();
    }

    /**
     * Determine status based on days to expiry
     */
    public static function determineStatus(int $daysToExpiry): string
    {
        if ($daysToExpiry < 0) {
            return 'expired';
        }
        if ($daysToExpiry <= 3) {
            return 'critical';
        }
        if ($daysToExpiry <= 7) {
            return 'urgent';
        }
        return 'approaching';
    }
}
