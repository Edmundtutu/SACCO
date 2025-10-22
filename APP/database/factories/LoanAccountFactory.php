<?php

namespace Database\Factories;

use App\Models\LoanAccount;
use App\Models\LoanProduct;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoanAccountFactory extends Factory
{
    protected $model = LoanAccount::class;

    public function definition(): array
    {
        $principal = $this->faker->randomFloat(2, 50000, 5000000);
        $interestRate = $this->faker->randomFloat(2, 10, 25);
        $processingFee = $principal * 0.02; // 2% processing fee
        $insuranceFee = $principal * 0.01; // 1% insurance
        $months = $this->faker->randomElement([6, 12, 18, 24, 36]);
        
        // Calculate total with simple interest
        $interest = ($principal * $interestRate * $months) / (100 * 12);
        $totalAmount = $principal + $interest + $processingFee + $insuranceFee;
        $monthlyPayment = $totalAmount / $months;
        
        $totalPaid = $this->faker->randomFloat(2, 0, $totalAmount * 0.5);
        $outstanding = $totalAmount - $totalPaid;
        
        return [
            'loan_product_id' => LoanProduct::factory(),
            'principal_amount' => $principal,
            'interest_rate' => $interestRate,
            'processing_fee' => $processingFee,
            'insurance_fee' => $insuranceFee,
            'total_amount' => $totalAmount,
            'repayment_period_months' => $months,
            'monthly_payment' => $monthlyPayment,
            'outstanding_balance' => $outstanding,
            'principal_balance' => $principal * ($outstanding / $totalAmount),
            'interest_balance' => $interest * ($outstanding / $totalAmount),
            'penalty_balance' => 0,
            'total_paid' => $totalPaid,
            'application_date' => $this->faker->dateTimeBetween('-1 year', '-6 months'),
            'approval_date' => $this->faker->dateTimeBetween('-6 months', '-3 months'),
            'disbursement_date' => $this->faker->dateTimeBetween('-3 months', '-1 month'),
            'first_payment_date' => $this->faker->dateTimeBetween('-2 months'),
            'maturity_date' => $this->faker->dateTimeBetween('+6 months', '+2 years'),
            'purpose' => $this->faker->sentence(),
            'collateral_description' => $this->faker->optional()->sentence(),
            'collateral_value' => $this->faker->optional()->randomFloat(2, 50000, 10000000),
            'approved_by' => User::factory(),
            'disbursed_by' => User::factory(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'approval_date' => null,
            'disbursement_date' => null,
            'first_payment_date' => null,
            'maturity_date' => null,
            'approved_by' => null,
            'disbursed_by' => null,
        ]);
    }

    public function disbursed(): static
    {
        return $this->state(fn (array $attributes) => [
            'disbursement_date' => now()->subMonths(rand(1, 6)),
        ]);
    }
}
