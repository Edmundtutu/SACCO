<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class SavingsAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'savings_product_id',
        'balance',
        'available_balance',
        'minimum_balance',
        'interest_earned',
        'interest_rate',
        'last_interest_calculation',
        'maturity_date',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'available_balance' => 'decimal:2',
        'minimum_balance' => 'decimal:2',
        'interest_earned' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'last_interest_calculation' => 'date',
        'maturity_date' => 'date',
    ];

    /**
     * Get the parent account record
     */
    public function account(): MorphOne
    {
        return $this->morphOne(Account::class, 'accountable');
    }

    /**
     * Savings product for this account
     */
    public function savingsProduct(): BelongsTo
    {
        return $this->belongsTo(SavingsProduct::class);
    }

    /**
     * Get member through account relationship
     */
    public function getMemberAttribute()
    {
        return $this->account?->member;
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
        // Check if account is active through parent account
        if ($this->account?->status !== 'active') {
            return false;
        }

        // Check if savings product allows partial withdrawals
        if ($this->savingsProduct && !$this->savingsProduct->allow_partial_withdrawals) {
            return false;
        }

        $remainingBalance = $this->available_balance - $amount;
        return $remainingBalance >= $this->minimum_balance;
    }

    /**
     * Check if this is a wallet account
     */
    public function isWallet(): bool
    {
        return $this->savingsProduct?->type === 'wallet';
    }

    /**
     * Scope to get wallet accounts
     */
    public function scopeWallet($query)
    {
        return $query->whereHas('savingsProduct', function ($q) {
            $q->where('type', 'wallet');
        });
    }

    /**
     * Scope to get active accounts
     */
    public function scopeActive($query)
    {
        return $query->whereHas('account', function ($q) {
            $q->where('status', 'active');
        });
    }
}
