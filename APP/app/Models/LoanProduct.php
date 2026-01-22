<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanProduct extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'minimum_amount',
        'maximum_amount',
        'interest_rate',
        'interest_calculation',
        'minimum_period_months',
        'maximum_period_months',
        'processing_fee_rate',
        'insurance_fee_rate',
        'required_guarantors',
        'guarantor_savings_multiplier',
        'grace_period_days',
        'penalty_rate',
        'minimum_savings_months',
        'savings_to_loan_ratio',
        'require_collateral',
        'is_active',
        'eligibility_criteria',
        'required_documents',
    ];

    protected $casts = [
        'minimum_amount' => 'decimal:2',
        'maximum_amount' => 'decimal:2',
        'interest_rate' => 'decimal:2',
        'processing_fee_rate' => 'decimal:2',
        'insurance_fee_rate' => 'decimal:2',
        'guarantor_savings_multiplier' => 'decimal:1',
        'penalty_rate' => 'decimal:2',
        'minimum_savings_months' => 'decimal:1',
        'savings_to_loan_ratio' => 'decimal:1',
        'require_collateral' => 'boolean',
        'is_active' => 'boolean',
        'eligibility_criteria' => 'array',
        'required_documents' => 'array',
    ];

    /**
     * Loans using this loan product
     */
    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    /**
     * Get active loan products
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get loan products by type
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Calculate monthly payment for reducing balance method
     */
    public function calculateMonthlyPayment(float $principal, int $months): float
    {
        $monthlyRate = $this->interest_rate / 100 / 12;
        
        if ($this->interest_calculation === 'flat_rate') {
            // Flat rate calculation
            $totalInterest = $principal * ($this->interest_rate / 100) * ($months / 12);
            return ($principal + $totalInterest) / $months;
        } else {
            // Reducing balance calculation
            if ($monthlyRate == 0) {
                return $principal / $months;
            }
            
            return $principal * ($monthlyRate * pow(1 + $monthlyRate, $months)) / 
                   (pow(1 + $monthlyRate, $months) - 1);
        }
    }

    /**
     * Calculate processing fee
     */
    public function calculateProcessingFee(float $amount): float
    {
        return $amount * ($this->processing_fee_rate / 100);
    }

    /**
     * Calculate insurance fee
     */
    public function calculateInsuranceFee(float $amount): float
    {
        return $amount * ($this->insurance_fee_rate / 100);
    }

    /**
     * Check if member is eligible for this loan product
     */
    public function isEligible(User $member, float $requestedAmount): array
    {
        $errors = [];

        // Check amount limits
        if ($requestedAmount < $this->minimum_amount) {
            $errors[] = "Minimum loan amount is {$this->minimum_amount}";
        }

        if ($requestedAmount > $this->maximum_amount) {
            $errors[] = "Maximum loan amount is {$this->maximum_amount}";
        }

        // Check savings history
        $savingsBalance = $member->getTotalSavingsBalance();
        $requiredSavings = $requestedAmount / $this->savings_to_loan_ratio;
        
        if ($savingsBalance < $requiredSavings) {
            $errors[] = "Insufficient savings balance. Required: {$requiredSavings}";
        }

        // Check if member has existing active loans
        $activeLoanBalance = $member->getActiveLoanBalance();
        if ($activeLoanBalance > 0) {
            $errors[] = "Member has existing active loan";
        }

        return [
            'eligible' => empty($errors),
            'errors' => $errors
        ];
    }
}