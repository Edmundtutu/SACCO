<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'member_id',
        'accountable_type',
        'accountable_id',
        'status',
        'closure_reason',
        'closed_at',
        'closed_by',
        'last_transaction_date'
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'last_transaction_date' => 'datetime',
    ];

    /**
     * Generate unique account number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($account) {
            if (empty($account->account_number)) {
                $account->account_number = 'ACC' . str_pad(
                    (static::max('id') ?? 0) + 1,
                    8,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });
    }

    /**
     * Member who owns this account
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Get the underlying accountable model (SavingsAccount, LoanAccount, or ShareAccount)
     */
    public function accountable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * User who closed this account
     */
    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    /**
     * Transactions for this account
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get active accounts
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Helper: Check if this is a savings account
     */
    public function isSavingsAccount(): bool
    {
        return $this->accountable_type === SavingsAccount::class;
    }

    /**
     * Helper: Check if this is a loan account
     */
    public function isLoanAccount(): bool
    {
        return $this->accountable_type === LoanAccount::class;
    }

    /**
     * Helper: Check if this is a share account
     */
    public function isShareAccount(): bool
    {
        return $this->accountable_type === ShareAccount::class;
    }

    /**
     * Helper: Get the account type as a string
     */
    public function getAccountTypeAttribute(): string
    {
        return match($this->accountable_type) {
            SavingsAccount::class => 'savings',
            LoanAccount::class => 'loan',
            ShareAccount::class => 'share',
            default => 'unknown',
        };
    }

    /**
     * Helper: Delegate balance updates to accountable model
     */
    public function updateBalance(float $amount, string $type = 'credit'): void
    {
        if ($this->accountable && method_exists($this->accountable, 'updateBalance')) {
            $this->accountable->updateBalance($amount, $type);
        }
    }

    /**
     * Helper: Delegate withdrawal check to accountable model
     */
    public function canWithdraw(float $amount): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->accountable && method_exists($this->accountable, 'canWithdraw')) {
            return $this->accountable->canWithdraw($amount);
        }

        return false;
    }

    /**
     * Scope: Get accounts by type
     */
    public function scopeOfType($query, string $type)
    {
        $modelClass = match($type) {
            'savings' => SavingsAccount::class,
            'loan' => LoanAccount::class,
            'share' => ShareAccount::class,
            default => null,
        };

        if ($modelClass) {
            return $query->where('accountable_type', $modelClass);
        }

        return $query;
    }
}
