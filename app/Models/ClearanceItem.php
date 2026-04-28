<?php

namespace App\Models;

use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClearanceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'stock_in_item_id',
        'product_id',
        'discount_rule_id',
        'expiry_date',
        'days_to_expiry',
        'status',
        'quantity',
        'original_price',
        'suggested_discount_pct',
        'clearance_price',
        'action_type',
        'actioned_at',
        'actioned_by',
        'notes',
        'approval_status',
        'suggested_at',
        'suggested_by',
        'approval_notes',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'actioned_at' => 'datetime',
        'suggested_at' => 'datetime',
    ];

    // Approval status constants
    const APPROVAL_MANUAL = 'manual';
    const APPROVAL_AUTO_SUGGESTED = 'auto_suggested';
    const APPROVAL_PENDING = 'pending_approval';
    const APPROVAL_APPROVED = 'approved';
    const APPROVAL_DECLINED = 'declined';
    const APPROVAL_REJECTED = 'rejected';
    const APPROVAL_REVERSED = 'reversed';

    // Status constants
    const STATUS_APPROACHING = 'approaching';
    const STATUS_URGENT = 'urgent';
    const STATUS_CRITICAL = 'critical';
    const STATUS_EXPIRED = 'expired';
    const STATUS_ACTIONED = 'actioned';

    // Action type constants
    const ACTION_DISCOUNT = 'discount';
    const ACTION_DONATE = 'donate';
    const ACTION_DISPOSE = 'dispose';

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function stockInItem(): BelongsTo
    {
        return $this->belongsTo(StockInItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function discountRule(): BelongsTo
    {
        return $this->belongsTo(ClearanceDiscountRule::class, 'discount_rule_id');
    }

    public function actionedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    public function suggestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suggested_by');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(ClearanceAction::class);
    }

    /**
     * Calculate days to expiry
     */
    public function calculateDaysToExpiry(): int
    {
        return (int) Carbon::today()->diffInDays($this->expiry_date, false);
    }

    /**
     * Update status based on days to expiry
     */
    public function updateStatus(): void
    {
        $days = $this->calculateDaysToExpiry();
        $this->days_to_expiry = $days;

        if ($this->status !== self::STATUS_ACTIONED) {
            $this->status = ClearanceDiscountRule::determineStatus($days);
        }

        $this->save();
    }

    /**
     * Apply discount to this item
     */
    public function applyDiscount(float $discountPercentage, ?float $customPrice = null): void
    {
        $this->suggested_discount_pct = $discountPercentage;
        $this->clearance_price = $customPrice ?? ($this->original_price * (1 - $discountPercentage / 100));
        $this->action_type = self::ACTION_DISCOUNT;
        $this->status = self::STATUS_ACTIONED;
        $this->actioned_at = now();
        $this->actioned_by = auth()->id();
        $this->save();

        // Create action record
        ClearanceAction::create([
            'branch_id' => $this->branch_id,
            'clearance_item_id' => $this->id,
            'user_id' => auth()->id(),
            'action_type' => 'discount',
            'quantity' => $this->quantity,
            'original_value' => $this->original_price * $this->quantity,
            'action_value' => $this->clearance_price,
            'recovered_value' => $this->clearance_price * $this->quantity,
            'loss_value' => ($this->original_price - $this->clearance_price) * $this->quantity,
        ]);
    }

    /**
     * Record donation for this item
     */
    public function recordDonation(int $quantity, string $organization, ?string $contact = null, ?string $address = null, ?string $notes = null): Donation
    {
        $donation = Donation::create([
            'branch_id' => $this->branch_id,
            'clearance_item_id' => $this->id,
            'user_id' => auth()->id(),
            'organization_name' => $organization,
            'organization_contact' => $contact,
            'organization_address' => $address,
            'total_items' => $quantity,
            'total_value' => $this->original_price * $quantity,
            'receipt_number' => 'DON-' . str_pad((string) Donation::max('id') + 1, 6, '0', STR_PAD_LEFT),
            'notes' => $notes,
            'donated_at' => now(),
        ]);

        // Create donation item
        DonationItem::create([
            'donation_id' => $donation->id,
            'product_id' => $this->product_id,
            'stock_in_item_id' => $this->stock_in_item_id,
            'quantity' => $quantity,
            'unit_value' => $this->original_price,
            'total_value' => $this->original_price * $quantity,
            'expiry_date' => $this->expiry_date,
        ]);

        // Update clearance item — always mark as actioned (keep original quantity for records/reversal)
        $this->action_type = self::ACTION_DONATE;
        $this->status = self::STATUS_ACTIONED;
        $this->actioned_at = now();
        $this->actioned_by = auth()->id();
        $this->save();

        // Create action record
        ClearanceAction::create([
            'branch_id' => $this->branch_id,
            'clearance_item_id' => $this->id,
            'user_id' => auth()->id(),
            'action_type' => 'donate',
            'quantity' => $quantity,
            'original_value' => $this->original_price * $quantity,
            'recovered_value' => 0,
            'loss_value' => $this->original_price * $quantity,
            'metadata' => ['donation_id' => $donation->id],
        ]);

        return $donation;
    }

    /**
     * Record disposal for this item
     */
    public function recordDisposal(int $quantity, string $reason, ?string $method = null, ?string $notes = null, ?string $photoPath = null): Disposal
    {
        $disposal = Disposal::create([
            'branch_id' => $this->branch_id,
            'clearance_item_id' => $this->id,
            'user_id' => auth()->id(),
            'disposal_reason' => $reason,
            'reason_details' => $notes,
            'total_items' => $quantity,
            'total_loss' => $this->original_price * $quantity,
            'disposal_method' => $method,
            'photo_path' => $photoPath,
            'disposed_at' => now(),
        ]);

        // Create disposal item
        DisposalItem::create([
            'disposal_id' => $disposal->id,
            'product_id' => $this->product_id,
            'stock_in_item_id' => $this->stock_in_item_id,
            'quantity' => $quantity,
            'unit_cost' => $this->original_price,
            'total_loss' => $this->original_price * $quantity,
            'expiry_date' => $this->expiry_date,
        ]);

        // Update clearance item — always mark as actioned (keep original quantity for records/reversal)
        $this->action_type = self::ACTION_DISPOSE;
        $this->status = self::STATUS_ACTIONED;
        $this->actioned_at = now();
        $this->actioned_by = auth()->id();
        $this->save();

        // Create action record
        ClearanceAction::create([
            'branch_id' => $this->branch_id,
            'clearance_item_id' => $this->id,
            'user_id' => auth()->id(),
            'action_type' => 'dispose',
            'quantity' => $quantity,
            'original_value' => $this->original_price * $quantity,
            'recovered_value' => 0,
            'loss_value' => $this->original_price * $quantity,
            'metadata' => ['disposal_id' => $disposal->id],
        ]);

        return $disposal;
    }

    /**
     * Scope for pending items (not yet actioned)
     */
    public function scopePending($query)
    {
        return $query->where('status', '!=', self::STATUS_ACTIONED);
    }

    /**
     * Scope for items requiring attention
     */
    public function scopeRequiresAttention($query)
    {
        return $query->whereIn('status', [self::STATUS_URGENT, self::STATUS_CRITICAL, self::STATUS_EXPIRED]);
    }

    /**
     * Scope for pending approval items (auto-suggested, awaiting manager review)
     */
    public function scopePendingApproval($query)
    {
        return $query->whereIn('approval_status', [
            self::APPROVAL_AUTO_SUGGESTED,
            self::APPROVAL_PENDING,
        ]);
    }

    /**
     * Approve a suggested item for clearance
     */
    public function approve(?string $notes = null): void
    {
        $this->approval_status = self::APPROVAL_APPROVED;
        $this->approval_notes = $notes;
        $this->save();

        ActivityLogger::log(
            'clearance.approved',
            $this,
            "Approved clearance for {$this->product->name}",
            ['notes' => $notes],
            $this->branch_id
        );
    }

    /**
     * Reject a suggested item (remove from clearance consideration)
     */
    public function reject(?string $notes = null): void
    {
        $this->approval_status = self::APPROVAL_REJECTED;
        $this->approval_notes = $notes;
        $this->action_type = 'rejected';
        $this->status = self::STATUS_ACTIONED;
        $this->actioned_at = now();
        $this->actioned_by = auth()->id();
        $this->save();

        ActivityLogger::log(
            'clearance.rejected',
            $this,
            "Rejected clearance for {$this->product->name}",
            ['notes' => $notes],
            $this->branch_id
        );
    }

    /**
     * Decline a suggested item (temporary rejection, can be re-suggested later)
     */
    public function decline(?string $notes = null): void
    {
        $this->approval_status = self::APPROVAL_DECLINED;
        $this->approval_notes = $notes;
        $this->action_type = 'declined';
        $this->status = self::STATUS_ACTIONED;
        $this->actioned_at = now();
        $this->actioned_by = auth()->id();
        $this->save();

        ActivityLogger::log(
            'clearance.declined',
            $this,
            "Declined clearance for {$this->product->name}",
            ['notes' => $notes, 'temporary' => true],
            $this->branch_id
        );
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_APPROACHING => 'yellow',
            self::STATUS_URGENT => 'orange',
            self::STATUS_CRITICAL => 'red',
            self::STATUS_EXPIRED => 'gray',
            self::STATUS_ACTIONED => 'green',
            default => 'gray',
        };
    }

    /**
     * Get approval status badge
     */
    public function getApprovalBadgeAttribute(): string
    {
        return match ($this->approval_status) {
            self::APPROVAL_MANUAL => '👤 Manual',
            self::APPROVAL_AUTO_SUGGESTED => '🤖 Auto-Suggested',
            self::APPROVAL_PENDING => '⏳ Pending',
            self::APPROVAL_APPROVED => '✅ Approved',
            self::APPROVAL_REJECTED => '❌ Rejected',
            default => 'Unknown',
        };
    }
}
