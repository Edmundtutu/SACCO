<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class LoanAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_disbursed_amount',
        'total_repaid_amount',
        'current_outstanding',
        'linked_savings_account',
        'min_loan_limit',
        'max_loan_limit',
        'repayment_frequency_type',
        'status_notes',
        'last_activity_date',
        'account_features',
        'audit_trail',
        'remarks',
    ];

    protected $casts = [
        'total_disbursed_amount' => 'decimal:2',
        'total_repaid_amount' => 'decimal:2',
        'current_outstanding' => 'decimal:2',
        'min_loan_limit' => 'decimal:2',
        'max_loan_limit' => 'decimal:2',
        'account_features' => 'array',
        'audit_trail' => 'array',
        'last_activity_date' => 'datetime',
    ];

    /**
     * Get the polymorphic account record
     */
    public function account(): MorphOne
    {
        return $this->morphOne(Account::class, 'accountable');
    }

    /**
     * Get all loans under this loan account
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class, 'loan_account_id');
    }

    /**
     * Get active loans
     */
    public function activeLoans(): HasMany
    {
        return $this->loans()->whereIn('status', ['disbursed', 'active']);
    }

    /**
     * Get linked savings account
     */
    public function linkedSavingsAccount(): BelongsTo
    {
        return $this->belongsTo(SavingsAccount::class, 'linked_savings_account');
    }

    /**
     * Record a new loan disbursement
     */
    public function recordDisbursement(float $amount): void
    {
        $this->total_disbursed_amount += $amount;
        $this->current_outstanding += $amount;
        $this->last_activity_date = now();
        $this->save();
    }

    /**
     * Record a loan repayment
     */
    public function recordRepayment(float $amount): void
    {
        $this->total_repaid_amount += $amount;
        $this->current_outstanding -= $amount;
        
        if ($this->current_outstanding < 0) {
            $this->current_outstanding = 0;
        }
        
        $this->last_activity_date = now();
        $this->save();
    }

    /**
     * Check if new loan can be approved based on limits
     */
    public function canAccommodateNewLoan(float $amount): bool
    {
        if ($this->max_loan_limit && $amount > $this->max_loan_limit) {
            return false;
        }
        
        if ($amount < $this->min_loan_limit) {
            return false;
        }
        
        return true;
    }

    /**
     * Get total number of loans
     */
    public function getTotalLoansAttribute(): int
    {
        return $this->loans()->count();
    }

    /**
     * Get number of active loans
     */
    public function getActiveLoansCountAttribute(): int
    {
        return $this->activeLoans()->count();
    }
}
