<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ShareAccount extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'share_units',
        'share_price',
        'total_share_value',
        'dividends_earned',
        'dividends_pending',
        'dividends_paid',
        'account_class',
        'locked_shares',
        'membership_fee_paid',
        'bonus_shares_earned',
        'min_balance_required',
        'max_balance_limit',
        'account_features',
        'audit_trail',
        'remarks',
        'last_activity_date',
    ];

    /**
     * Attributes that should never be mass-assigned
     */
    protected $guarded = [
        'tenant_id',
    ];

    protected $casts = [
        'share_units' => 'integer',
        'share_price' => 'decimal:2',
        'total_share_value' => 'decimal:2',
        'dividends_earned' => 'decimal:2',
        'dividends_pending' => 'decimal:2',
        'dividends_paid' => 'decimal:2',
        'locked_shares' => 'integer',
        'bonus_shares_earned' => 'integer',
        'min_balance_required' => 'integer',
        'max_balance_limit' => 'integer',
        'membership_fee_paid' => 'boolean',
        'account_features' => 'json',
        'audit_trail' => 'json',
        'last_activity_date' => 'date',
    ];

    /**
     * Generate unique certificate number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($share) {
            // Calculate total value
            if (empty($share->total_share_value)) {
                $share->total_share_value = $share->share_units * $share->share_price;
            }
        });
    }

    /**
     * Get the polymorphic account record
     */
    public function account(): MorphOne
    {
        return $this->morphOne(Account::class, 'accountable');
    }

    /**
     * Get all share certificates under this account
     */
    public function shares(): HasMany
    {
        return $this->hasMany(Share::class, 'share_account_id');
    }

    /**
     * Get active share certificates
     */
    public function activeShares(): HasMany
    {
        return $this->shares()->where('status', 'active');
    }

    /**
     * Get member through account relationship
     */
    public function getMemberAttribute()
    {
        return $this->account?->member;
    }

    /**
     * Record share purchase
     */
    public function recordSharePurchase(int $units, float $pricePerShare): void
    {
        $this->share_units += $units;
        $this->share_price = $pricePerShare; // Update to latest price
        $this->total_share_value = $this->share_units * $this->share_price;
        $this->last_activity_date = now();
        $this->save();
    }

    /**
     * Record share transfer/redemption
     */
    public function recordShareRemoval(int $units): bool
    {
        $availableShares = $this->share_units - $this->locked_shares;

        if ($units > $availableShares) {
            return false;
        }

        $this->share_units -= $units;
        $this->total_share_value = $this->share_units * $this->share_price;
        $this->last_activity_date = now();
        $this->save();

        return true;
    }

    /**
     * Record dividend earned
     */
    public function recordDividend(float $amount): void
    {
        $this->dividends_earned += $amount;
        $this->dividends_pending += $amount;
        $this->save();
    }

    /**
     * Record dividend payment
     */
    public function recordDividendPayment(float $amount): void
    {
        $this->dividends_pending -= $amount;
        $this->dividends_paid += $amount;

        if ($this->dividends_pending < 0) {
            $this->dividends_pending = 0;
        }

        $this->save();
    }

    /**
     * Add bonus shares
     */
    public function addBonusShares(int $units): void
    {
        $this->share_units += $units;
        $this->bonus_shares_earned += $units;
        $this->total_share_value = $this->share_units * $this->share_price;
        $this->save();
    }

    /**
     * Get available (unlocked) shares
     */
    public function getAvailableSharesAttribute(): int
    {
        return max(0, $this->share_units - $this->locked_shares);
    }

    /**
     * Check if account meets minimum balance requirement
     */
    public function meetsMinimumRequirement(): bool
    {
        return $this->share_units >= $this->min_balance_required;
    }

    /**
     * Get total shares owned by member
     */
    public static function getTotalSharesByMember(int $memberId): int
    {
        return static::whereHas('account', function ($q) use ($memberId) {
            $q->where('member_id', $memberId)->where('status', 'active');
        })->sum('share_units');
    }

    /**
     * Get total share value owned by member
     */
    public static function getTotalShareValueByMember(int $memberId): float
    {
        return static::whereHas('account', function ($q) use ($memberId) {
            $q->where('member_id', $memberId)->where('status', 'active');
        })->sum('total_share_value');
    }
}
