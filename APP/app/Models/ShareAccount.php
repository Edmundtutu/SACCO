<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class ShareAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'certificate_number',
        'shares_count',
        'share_value',
        'total_value',
        'purchase_date',
        'notes',
        'issued_by',
    ];

    protected $casts = [
        'shares_count' => 'integer',
        'share_value' => 'decimal:2',
        'total_value' => 'decimal:2',
        'purchase_date' => 'date',
    ];

    /**
     * Generate unique certificate number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($share) {
            if (empty($share->certificate_number)) {
                $share->certificate_number = 'SHR' . now()->format('Y') . str_pad(
                    (static::max('id') ?? 0) + 1,
                    6,
                    '0',
                    STR_PAD_LEFT
                );
            }

            // Calculate total value
            if (empty($share->total_value)) {
                $share->total_value = $share->shares_count * $share->share_value;
            }
        });
    }

    /**
     * Get the parent account record
     */
    public function account(): MorphOne
    {
        return $this->morphOne(Account::class, 'accountable');
    }

    /**
     * User who issued these shares
     */
    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    /**
     * Get member through account relationship
     */
    public function getMemberAttribute()
    {
        return $this->account?->member;
    }

    /**
     * Add more shares to this account
     */
    public function addShares(int $count, float $value): void
    {
        $this->shares_count += $count;
        $this->share_value = $value; // Update to latest share value
        $this->total_value = $this->shares_count * $this->share_value;
        $this->save();
    }

    /**
     * Remove shares from this account (e.g., for transfer or sale)
     */
    public function removeShares(int $count): bool
    {
        if ($count > $this->shares_count) {
            return false;
        }

        $this->shares_count -= $count;
        $this->total_value = $this->shares_count * $this->share_value;
        $this->save();
        
        return true;
    }

    /**
     * Scope to get active shares
     */
    public function scopeActive($query)
    {
        return $query->whereHas('account', function ($q) {
            $q->where('status', 'active');
        });
    }

    /**
     * Get total shares owned by member
     */
    public static function getTotalSharesByMember(int $memberId): int
    {
        return static::whereHas('account', function ($q) use ($memberId) {
            $q->where('member_id', $memberId)->where('status', 'active');
        })->sum('shares_count');
    }

    /**
     * Get total share value owned by member
     */
    public static function getTotalShareValueByMember(int $memberId): float
    {
        return static::whereHas('account', function ($q) use ($memberId) {
            $q->where('member_id', $memberId)->where('status', 'active');
        })->sum('total_value');
    }
}
