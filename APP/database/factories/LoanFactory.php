<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Account;
use App\Models\LoanProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Loan>
 */
class LoanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $loanProductId = LoanProduct::query()->inRandomOrder()->value('id');
        if (!$loanProductId) {
            $loanProductId = LoanProduct::create([
                'name' => 'Personal Loan',
                'code' => 'PL' . $this->faker->unique()->numerify('###'),
                'description' => 'Autocreated by factory',
                'type' => 'personal',
                'minimum_amount' => 1000,
                'maximum_amount' => 100000,
                'interest_rate' => 12.0,
                'interest_calculation' => 'reducing_balance',
                'minimum_period_months' => 6,
                'maximum_period_months' => 36,
                'processing_fee_rate' => 2.0,
                'insurance_fee_rate' => 1.0,
                'required_guarantors' => 1,
                'guarantor_savings_multiplier' => 2.0,
                'grace_period_days' => 0,
                'penalty_rate' => 2.0,
                'minimum_savings_months' => 0,
                'savings_to_loan_ratio' => 1.0,
                'require_collateral' => false,
                'is_active' => true,
            ])->id;
        }

        $principal = $this->faker->randomFloat(2, 1000, 100000);
        $interestRate = $this->faker->randomFloat(2, 5, 20);
        $processingFee = round($principal * 0.02, 2);
        $insuranceFee = round($principal * 0.01, 2);
        $repaymentMonths = $this->faker->numberBetween(6, 36);

        $monthlyRate = $interestRate / 100 / 12;
        $monthlyPayment = $monthlyRate > 0
            ? $principal * ($monthlyRate * pow(1 + $monthlyRate, $repaymentMonths)) / (pow(1 + $monthlyRate, $repaymentMonths) - 1)
            : $principal / $repaymentMonths;
        $totalAmount = $principal + ($monthlyPayment * $repaymentMonths - $principal) + $processingFee + $insuranceFee;

        $status = $this->faker->randomElement(['pending', 'approved', 'disbursed', 'active']);

        return [
            'loan_number' => 'LN' . str_pad((string)$this->faker->unique()->numberBetween(1, 99999999), 8, '0', STR_PAD_LEFT),
            'member_id' => User::factory(),
            'loan_product_id' => $loanProductId,
            'principal_amount' => $principal,
            'interest_rate' => $interestRate,
            'processing_fee' => $processingFee,
            'insurance_fee' => $insuranceFee,
            'total_amount' => $totalAmount,
            'repayment_period_months' => $repaymentMonths,
            'monthly_payment' => round($monthlyPayment, 2),
            'application_date' => now()->subDays($this->faker->numberBetween(1, 90))->toDateString(),
            'approval_date' => $this->faker->optional()->date(),
            'disbursement_date' => $this->faker->optional()->date(),
            'first_payment_date' => $this->faker->optional()->date(),
            'maturity_date' => $this->faker->optional()->date(),
            'status' => $status,
            'outstanding_balance' => $principal,
            'principal_balance' => $principal,
            'interest_balance' => 0,
            'penalty_balance' => 0,
            'total_paid' => 0,
            'purpose' => $this->faker->sentence(),
            'collateral_description' => $this->faker->optional()->sentence(),
            'collateral_value' => $this->faker->optional()->randomFloat(2, 1000, 20000),
            'rejection_reason' => null,
            'approved_by' => null,
            'disbursed_by' => null,
            'disbursement_account_id' => null,
        ];
    }
}
