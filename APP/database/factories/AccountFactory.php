<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\SavingsProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $status = $this->faker->randomElement(['active', 'inactive', 'dormant', 'closed']);

        // Try to pick an existing savings product, otherwise create a minimal one
        $savingsProductId = SavingsProduct::query()->inRandomOrder()->value('id');
        if (!$savingsProductId) {
            $savingsProductId = SavingsProduct::create([
                'name' => 'Voluntary Savings',
                'code' => 'VS' . $this->faker->unique()->numerify('###'),
                'description' => 'Autocreated by factory',
                'type' => 'voluntary',
                'minimum_balance' => 0,
                'maximum_balance' => null,
                'interest_rate' => 3.0,
                'interest_calculation' => 'simple',
                'interest_payment_frequency' => 'annually',
                'minimum_monthly_contribution' => null,
                'maturity_period_months' => null,
                'withdrawal_fee' => 0,
                'allow_partial_withdrawals' => true,
                'minimum_notice_days' => 0,
                'is_active' => true,
            ])->id;
        }

        $balance = $this->faker->randomFloat(2, 0, 50000);
        $available = $this->faker->boolean(90) ? $balance : max(0, $balance - $this->faker->randomFloat(2, 0, 1000));

        return [
            'account_number' => 'ACC' . str_pad((string)$this->faker->unique()->numberBetween(1, 99999999), 8, '0', STR_PAD_LEFT),
            'member_id' => User::factory(),
            'account_type' => $this->faker->randomElement(['savings', 'save_for_target']),
            'savings_product_id' => $savingsProductId,
            'balance' => $balance,
            'available_balance' => $available,
            'minimum_balance' => $this->faker->randomElement([0, 1000, 5000]),
            'interest_earned' => $this->faker->randomFloat(2, 0, 2000),
            'last_interest_calculation' => $this->faker->optional()->date(),
            'maturity_date' => $this->faker->optional()->date(),
            'status' => $status,
            'last_transaction_date' => $this->faker->optional()->dateTimeThisYear(),
            'closure_reason' => $status === 'closed' ? $this->faker->sentence() : null,
            'closed_at' => $status === 'closed' ? now() : null,
            'closed_by' => null,
        ];
    }
}
