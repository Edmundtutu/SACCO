<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class LoanAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_product_id',
        'principal_amount',
        'interest_rate',
        'processing_fee',
        'insurance_fee',
        'total_amount',
        'repayment_period_months',
        'monthly_payment',
        'outstanding_balance',
        'principal_balance',
        'interest_balance',
        'penalty_balance',
        'total_paid',
        'application_date',
        'approval_date',
        'disbursement_date',
        'first_payment_date',
        'maturity_date',
        'purpose',
        'collateral_description',
        'collateral_value',
        'rejection_reason',
        'approved_by',
        'disbursed_by',
        'disbursement_account_id',
    ];

    protected $casts = [
        'principal_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'processing_fee' => 'decimal:2',
        'insurance_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'principal_balance' => 'decimal:2',
        'interest_balance' => 'decimal:2',
        'penalty_balance' => 'decimal:2',
        'total_paid' => 'decimal:2',
        'collateral_value' => 'decimal:2',
        'application_date' => 'date',
        'approval_date' => 'date',
        'disbursement_date' => 'date',
        'first_payment_date' => 'date',
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
     * Loan product for this loan
     */
    public function loanProduct(): BelongsTo
    {
        return $this->belongsTo(LoanProduct::class);
    }

    /**
     * User who approved this loan
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * User who disbursed this loan
     */
    public function disbursedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    /**
     * Account where loan was disbursed
     */
    public function disbursementAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'disbursement_account_id');
    }

    /**
     * Get member through account relationship
     */
    public function getMemberAttribute()
    {
        return $this->account?->member;
    }

    /**
     * Update outstanding balance after payment
     */
    public function recordPayment(float $amount, array $breakdown = []): void
    {
        $this->total_paid += $amount;
        
        // Apply payment: penalties first, then interest, then principal
        if (isset($breakdown['penalty'])) {
            $this->penalty_balance -= $breakdown['penalty'];
        }
        if (isset($breakdown['interest'])) {
            $this->interest_balance -= $breakdown['interest'];
        }
        if (isset($breakdown['principal'])) {
            $this->principal_balance -= $breakdown['principal'];
        }
        
        $this->outstanding_balance = $this->principal_balance + $this->interest_balance + $this->penalty_balance;
        $this->save();
    }

    /**
     * Check if loan is fully paid
     */
    public function isFullyPaid(): bool
    {
        return $this->outstanding_balance <= 0;
    }

    /**
     * Check if loan is overdue
     */
    public function isOverdue(): bool
    {
        if (!$this->first_payment_date) {
            return false;
        }
        
        return now()->greaterThan($this->first_payment_date) && $this->outstanding_balance > 0;
    }

    /**
     * Scope to get active loans
     */
    public function scopeActive($query)
    {
        return $query->whereHas('account', function ($q) {
            $q->where('status', 'active');
        });
    }

    /**
     * Scope to get disbursed loans
     */
    public function scopeDisbursed($query)
    {
        return $query->whereNotNull('disbursement_date');
    }
}
