<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
{
    use HasFactory;

    protected $fillable = [
        'account_number',
        'member_id',
        'account_type',
        'savings_product_id',
        'balance',
        'available_balance',
        'minimum_balance',
        'interest_earned',
        'last_interest_calculation',
        'maturity_date',
        'status',
        'last_transaction_date',
        'closure_reason',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'minimum_balance' => 'decimal:2',
        'interest_earned' => 'decimal:2',
        'last_interest_calculation' => 'date',
        'maturity_date' => 'date',
        'last_transaction_date' => 'datetime',
        'closed_at' => 'datetime',
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
     * Savings product for this account
     */
    public function savingsProduct(): BelongsTo
    {
        return $this->belongsTo(SavingsProduct::class);
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
     * Update balances after transaction
     */
    public function updateBalance(float $amount, string $type = 'credit'): void
    {
        if ($type === 'credit') {
            $this->balance += $amount;
            $this->available_balance += $amount;
        } else {
            $this->balance -= $amount;
            $this->available_balance -= $amount;
        }

        $this->last_transaction_date = now();
        $this->save();
    }

    /**
     * Check if withdrawal is allowed
     */
    public function canWithdraw(float $amount): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if (!$this->savingsProduct->allow_partial_withdrawals) {
            return false;
        }

        $remainingBalance = $this->available_balance - $amount;
        return $remainingBalance >= $this->minimum_balance;
    }
}
