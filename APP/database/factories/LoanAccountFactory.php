<?php

namespace Database\Factories;

use App\Models\LoanAccount;
use App\Models\SavingsAccount;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * LoanAccount Factory - Creates account-level tracking
 * (NOT individual loan details - those are in Loan model)
 */
class LoanAccountFactory extends Factory
{
    protected $model = LoanAccount::class;

    public function definition(): array
    {
        // Account-level aggregate data
        $totalDisbursed = $this->faker->randomFloat(2, 100000, 5000000);
        $repaymentProgress = $this->faker->randomFloat(1, 0.2, 0.8); // 20% to 80% repaid
        $totalRepaid = $totalDisbursed * $repaymentProgress;
        $currentOutstanding = $totalDisbursed - $totalRepaid;
        
        return [
            // Account-level totals (aggregates from all loans)
            'total_disbursed_amount' => $totalDisbursed,
            'total_repaid_amount' => $totalRepaid,
            'current_outstanding' => $currentOutstanding,
            
            // Account configuration
            'linked_savings_account' => null, // Can be set with state
            'min_loan_limit' => $this->faker->randomElement([10000, 20000, 50000]),
            'max_loan_limit' => $this->faker->randomElement([500000, 1000000, 2000000, 5000000]),
            'repayment_frequency_type' => $this->faker->randomElement(['weekly', 'biweekly', 'monthly', 'quarterly']),
            
            // Status and tracking
            'status_notes' => $this->faker->optional(0.3)->sentence(),
            'last_activity_date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            
            // Flexible features
            'account_features' => [
                'auto_deduct_from_savings' => $this->faker->boolean(70),
                'sms_notifications' => $this->faker->boolean(80),
                'email_statements' => $this->faker->boolean(60),
            ],
            'audit_trail' => [
                [
                    'action' => 'account_created',
                    'date' => $this->faker->dateTimeBetween('-2 years', '-1 year')->format('Y-m-d H:i:s'),
                    'by' => 'System',
                ],
            ],
            'remarks' => $this->faker->optional(0.2)->sentence(),
        ];
    }

    /**
     * State: New loan account with no loans yet
     */
    public function fresh(): static
    {
        return $this->state(fn (array $attributes) => [
            'total_disbursed_amount' => 0,
            'total_repaid_amount' => 0,
            'current_outstanding' => 0,
            'last_activity_date' => null,
            'status_notes' => 'Account opened, no loans disbursed yet',
        ]);
    }

    /**
     * State: Account with active loans
     */
    public function withActiveLoans(): static
    {
        return $this->state(fn (array $attributes) => [
            'current_outstanding' => $this->faker->randomFloat(2, 50000, 1000000),
            'last_activity_date' => $this->faker->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * State: Link to a savings account
     */
    public function linkedToSavings(?SavingsAccount $savings = null): static
    {
        return $this->state(fn (array $attributes) => [
            'linked_savings_account' => $savings?->id ?? SavingsAccount::factory()->create()->id,
        ]);
    }

    /**
     * State: High limit account
     */
    public function highLimit(): static
    {
        return $this->state(fn (array $attributes) => [
            'min_loan_limit' => 100000,
            'max_loan_limit' => 10000000,
        ]);
    }
}
