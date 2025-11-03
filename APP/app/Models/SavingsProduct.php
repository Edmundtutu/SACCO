<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SavingsProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'minimum_balance',
        'maximum_balance',
        'interest_rate',
        'interest_calculation',
        'interest_payment_frequency',
        'minimum_monthly_contribution',
        'maturity_period_months',
        'withdrawal_fee',
        'allow_partial_withdrawals',
        'minimum_notice_days',
        'is_active',
        'additional_rules',
    ];

    protected $casts = [
        'minimum_balance' => 'decimal:2',
        'maximum_balance' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'minimum_monthly_contribution' => 'decimal:2',
        'withdrawal_fee' => 'decimal:2',
        'allow_partial_withdrawals' => 'boolean',
        'is_active' => 'boolean',
        'additional_rules' => 'array',
    ];

    /**
     * Accounts using this savings product
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(SavingsAccount::class);
    }

    /**
     * Get active savings products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get savings products by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Calculate interest for a given amount and period
     */
    public function calculateInterest(float $amount, int $days = 365): float
    {
        $rate = $this->interest_rate / 100;
        
        if ($this->interest_calculation === 'simple') {
            return $amount * $rate * ($days / 365);
        } else {
            // Compound interest (annually)
            $periods = $days / 365;
            return $amount * (pow(1 + $rate, $periods) - 1);
        }
    }
}