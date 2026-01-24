<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsGoal extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'member_id',
        'savings_account_id',
        'title',
        'description',
        'target_amount',
        'current_amount',
        'target_date',
        'status',
        'auto_nudge',
        'nudge_frequency',
        'last_nudged_at',
        'achieved_at',
        'metadata',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'target_date' => 'date',
        'auto_nudge' => 'boolean',
        'last_nudged_at' => 'datetime',
        'achieved_at' => 'datetime',
        'metadata' => 'array',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_PAUSED,
        self::STATUS_CANCELLED,
    ];

    public const NUDGE_DAILY = 'daily';
    public const NUDGE_WEEKLY = 'weekly';
    public const NUDGE_MONTHLY = 'monthly';

    public const NUDGE_FREQUENCIES = [
        self::NUDGE_DAILY,
        self::NUDGE_WEEKLY,
        self::NUDGE_MONTHLY,
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    public function savingsAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'savings_account_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function markCompleted(): void
    {
        if ($this->status !== self::STATUS_COMPLETED) {
            $this->status = self::STATUS_COMPLETED;
            $this->achieved_at = now();
        }
    }

    public function markActive(): void
    {
        $this->status = self::STATUS_ACTIVE;
        $this->achieved_at = null;
    }
}
