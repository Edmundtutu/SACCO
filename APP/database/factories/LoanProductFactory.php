<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LoanProduct>
 */
class LoanProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $type = $this->faker->randomElement(['personal', 'emergency', 'development', 'school_fees', 'business', 'asset_financing']);
        
        return [
            'name' => ucfirst($type) . ' Loan',
            'code' => strtoupper(substr($type, 0, 2)) . $this->faker->unique()->numerify('###'),
            'description' => $this->faker->sentence(),
            'type' => $type,
            'minimum_amount' => $this->faker->randomFloat(2, 1000, 10000),
            'maximum_amount' => $this->faker->randomFloat(2, 50000, 1000000),
            'interest_rate' => $this->faker->randomFloat(2, 5, 25),
            'interest_calculation' => $this->faker->randomElement(['flat_rate', 'reducing_balance']),
            'minimum_period_months' => $this->faker->numberBetween(1, 6),
            'maximum_period_months' => $this->faker->numberBetween(12, 60),
            'processing_fee_rate' => $this->faker->randomFloat(2, 0.5, 5),
            'insurance_fee_rate' => $this->faker->randomFloat(2, 0, 3),
            'required_guarantors' => $this->faker->numberBetween(1, 3),
            'guarantor_savings_multiplier' => $this->faker->randomFloat(1, 2, 5),
            'grace_period_days' => $this->faker->numberBetween(0, 30),
            'penalty_rate' => $this->faker->randomFloat(2, 1, 5),
            'minimum_savings_months' => $this->faker->randomFloat(1, 3, 12),
            'savings_to_loan_ratio' => $this->faker->randomFloat(1, 2, 5),
            'require_collateral' => $this->faker->boolean(30),
            'is_active' => $this->faker->boolean(90),
            'eligibility_criteria' => [
                'minimum_age' => 18,
                'maximum_age' => 65,
                'minimum_income' => 50000,
                'employment_status' => 'employed',
            ],
            'required_documents' => [
                'national_id',
                'payslip',
                'bank_statement',
                'employment_letter',
            ],
        ];
    }

    /**
     * Create personal loan product
     */
    public function personal()
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'personal',
            'name' => 'Personal Loan',
            'minimum_amount' => 5000,
            'maximum_amount' => 100000,
            'interest_rate' => 12.0,
            'minimum_period_months' => 6,
            'maximum_period_months' => 36,
            'required_guarantors' => 2,
            'require_collateral' => false,
        ]);
    }

    /**
     * Create emergency loan product
     */
    public function emergency()
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'emergency',
            'name' => 'Emergency Loan',
            'minimum_amount' => 1000,
            'maximum_amount' => 50000,
            'interest_rate' => 15.0,
            'minimum_period_months' => 3,
            'maximum_period_months' => 12,
            'required_guarantors' => 1,
            'require_collateral' => false,
        ]);
    }

    /**
     * Create development loan product
     */
    public function development()
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'development',
            'name' => 'Development Loan',
            'minimum_amount' => 50000,
            'maximum_amount' => 500000,
            'interest_rate' => 10.0,
            'minimum_period_months' => 12,
            'maximum_period_months' => 60,
            'required_guarantors' => 3,
            'require_collateral' => true,
        ]);
    }
}
