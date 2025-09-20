<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SavingsProduct>
 */
class SavingsProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $type = $this->faker->randomElement(['voluntary', 'compulsory', 'fixed_deposit', 'special']);
        
        return [
            'name' => $this->faker->words(2, true) . ' Savings',
            'code' => strtoupper($this->faker->lexify('???') . $this->faker->numerify('###')),
            'description' => $this->faker->sentence(),
            'type' => $type,
            'minimum_balance' => $this->faker->randomFloat(2, 0, 10000),
            'maximum_balance' => $this->faker->optional(0.7)->randomFloat(2, 100000, 1000000),
            'interest_rate' => $this->faker->randomFloat(2, 1, 15),
            'interest_calculation' => $this->faker->randomElement(['simple', 'compound']),
            'interest_payment_frequency' => $this->faker->randomElement(['monthly', 'quarterly', 'annually']),
            'minimum_monthly_contribution' => $this->faker->optional(0.6)->randomFloat(2, 1000, 10000),
            'maturity_period_months' => $type === 'fixed_deposit' ? $this->faker->randomElement([6, 12, 24, 36]) : null,
            'withdrawal_fee' => $this->faker->randomFloat(2, 0, 2000),
            'allow_partial_withdrawals' => $this->faker->boolean(80),
            'minimum_notice_days' => $this->faker->randomElement([0, 7, 14, 30]),
            'is_active' => $this->faker->boolean(90),
            'additional_rules' => $this->faker->optional(0.3)->randomElements([
                'requires_approval_for_large_withdrawals',
                'interest_calculation_daily',
                'early_withdrawal_penalty',
                'minimum_holding_period'
            ], $this->faker->numberBetween(1, 2)),
        ];
    }

    /**
     * Create a voluntary savings product
     */
    public function voluntary()
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'voluntary',
            'minimum_balance' => 0,
            'allow_partial_withdrawals' => true,
            'withdrawal_fee' => $this->faker->randomFloat(2, 0, 1000),
        ]);
    }

    /**
     * Create a fixed deposit product
     */
    public function fixedDeposit()
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fixed_deposit',
            'minimum_balance' => $this->faker->randomFloat(2, 10000, 50000),
            'allow_partial_withdrawals' => false,
            'maturity_period_months' => $this->faker->randomElement([6, 12, 24, 36]),
            'withdrawal_fee' => $this->faker->randomFloat(2, 1000, 5000),
        ]);
    }
}