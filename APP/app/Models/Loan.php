<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_number',
        'member_id',
        'loan_account_id',
        'loan_product_id',
        'principal_amount',
        'interest_rate',
        'processing_fee',
        'insurance_fee',
        'total_amount',
        'repayment_period_months',
        'monthly_payment',
        'application_date',
        'approval_date',
        'disbursement_date',
        'first_payment_date',
        'maturity_date',
        'status',
        'outstanding_balance',
        'principal_balance',
        'interest_balance',
        'penalty_balance',
        'total_paid',
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
     * Generate unique loan number
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($loan) {
            if (empty($loan->loan_number)) {
                $loan->loan_number = 'LN' . str_pad(
                    (static::max('id') ?? 0) + 1,
                    8,
                    '0',
                    STR_PAD_LEFT
                );
            }
        });
    }

    /**
     * Member who owns this loan
     */
    public function member(): BelongsTo
    {
        return $this->belongsTo(User::class, 'member_id');
    }

    /**
     * Parent loan account for this loan
     */
    public function loanAccount(): BelongsTo
    {
        return $this->belongsTo(LoanAccount::class);
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
     * Loan guarantors
     */
    public function loanGuarantors(): HasMany
    {
        return $this->hasMany(LoanGuarantor::class);
    }

    /**
     * Alias for loanGuarantors for backward compatibility
     */
    public function guarantors(): HasMany
    {
        return $this->loanGuarantors();
    }

    /**
     * Loan repayments
     */
    public function repayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    /**
     * Transactions related to this loan
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'related_loan_id');
    }

    /**
     * Get active loans
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['disbursed', 'active']);
    }

    /**
     * Get loans by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Calculate loan schedule
     */
    public function generateRepaymentSchedule(): array
    {
        $schedule = [];
        $startDate = $this->first_payment_date ?: $this->disbursement_date->addMonth();
        $balance = $this->principal_amount;
        $monthlyRate = $this->interest_rate / 100 / 12;

        for ($i = 1; $i <= $this->repayment_period_months; $i++) {
            $dueDate = $startDate->copy()->addMonths($i - 1);
            
            if ($this->loanProduct->interest_calculation === 'flat_rate') {
                $interestAmount = $this->principal_amount * ($this->interest_rate / 100) / 12;
                $principalAmount = $this->monthly_payment - $interestAmount;
            } else {
                $interestAmount = $balance * $monthlyRate;
                $principalAmount = $this->monthly_payment - $interestAmount;
                $balance -= $principalAmount;
            }

            $schedule[] = [
                'installment_number' => $i,
                'due_date' => $dueDate,
                'principal_amount' => round($principalAmount, 2),
                'interest_amount' => round($interestAmount, 2),
                'total_amount' => round($this->monthly_payment, 2),
                'balance_after' => round($balance, 2),
            ];
        }

        return $schedule;
    }

    /**
     * Apply payment to loan
     */
    public function applyPayment(float $amount): array
    {
        $allocation = [
            'penalty' => 0,
            'interest' => 0,
            'principal' => 0,
            'remaining' => $amount
        ];

        // First apply to penalties
        if ($this->penalty_balance > 0 && $allocation['remaining'] > 0) {
            $penaltyPayment = min($this->penalty_balance, $allocation['remaining']);
            $allocation['penalty'] = $penaltyPayment;
            $allocation['remaining'] -= $penaltyPayment;
            $this->penalty_balance -= $penaltyPayment;
        }

        // Then apply to interest
        if ($this->interest_balance > 0 && $allocation['remaining'] > 0) {
            $interestPayment = min($this->interest_balance, $allocation['remaining']);
            $allocation['interest'] = $interestPayment;
            $allocation['remaining'] -= $interestPayment;
            $this->interest_balance -= $interestPayment;
        }

        // Finally apply to principal
        if ($this->principal_balance > 0 && $allocation['remaining'] > 0) {
            $principalPayment = min($this->principal_balance, $allocation['remaining']);
            $allocation['principal'] = $principalPayment;
            $allocation['remaining'] -= $principalPayment;
            $this->principal_balance -= $principalPayment;
        }

        // Update total paid and outstanding balance
        $this->total_paid += $amount - $allocation['remaining'];
        $this->outstanding_balance = $this->principal_balance + $this->interest_balance + $this->penalty_balance;

        // Check if loan is fully paid
        if ($this->outstanding_balance <= 0.01) {
            $this->status = 'completed';
        }

        $this->save();

        return $allocation;
    }
}