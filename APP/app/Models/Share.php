<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Share extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'member_id',
        'share_account_id',
        'certificate_number',
        'shares_count',
        'share_value',
        'total_value',
        'purchase_date',
        'status',
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
     * Member who owns these shares
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Parent share account for this certificate
     */
    public function shareAccount(): BelongsTo
    {
        return $this->belongsTo(ShareAccount::class);
    }

    /**
     * User who processed this share transaction
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Transactions related to these shares
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'member_id', 'member_id')
            ->where('type', 'share_purchase');
    }

    /**
     * Get active shares
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get shares by member
     */
    public function scopeByMember($query, int $memberId)
    {
        return $query->where('member_id', $memberId);
    }

    /**
     * Get total shares owned by member
     */
    public static function getTotalSharesByMember(int $memberId): int
    {
        return static::where('member_id', $memberId)
            ->where('status', 'active')
            ->sum('shares_count');
    }

    /**
     * Get total share value owned by member
     */
    public static function getTotalShareValueByMember(int $memberId): float
    {
        return static::where('member_id', $memberId)
            ->where('status', 'active')
            ->sum('total_value');
    }
}
