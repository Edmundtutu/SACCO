<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\User;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $type = $this->faker->randomElement([
            'deposit', 'withdrawal', 'transfer', 'loan_disbursement', 'loan_repayment',
            'fee', 'interest', 'dividend', 'share_purchase', 'share_redemption'
        ]);
        $category = $this->faker->randomElement(['savings', 'loan', 'share', 'fee', 'administrative']);

        $amount = $this->faker->randomFloat(2, 10, 20000);
        $fee = in_array($type, ['withdrawal', 'transfer', 'loan_disbursement', 'loan_repayment', 'fee'])
            ? $this->faker->randomFloat(2, 0, min(500, $amount * 0.05))
            : 0;
        $net = $amount - $fee;

        return [
            'transaction_number' => 'TXN' . str_pad((string)$this->faker->unique()->numberBetween(1, 9999999999), 10, '0', STR_PAD_LEFT),
            'member_id' => User::factory(),
            'account_id' => Account::factory(),
            'type' => $type,
            'category' => $category,
            'amount' => $amount,
            'fee_amount' => $fee,
            'net_amount' => $net,
            'balance_before' => null,
            'balance_after' => null,
            'description' => $this->faker->sentence(),
            'payment_method' => $this->faker->optional()->randomElement(['cash', 'bank_transfer', 'mobile_money', 'check', 'internal_transfer']),
            'payment_reference' => $this->faker->optional()->uuid(),
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'reversed']),
            'transaction_date' => $this->faker->dateTimeThisYear(),
            'value_date' => $this->faker->optional()->dateTimeThisYear(),
            'related_loan_id' => in_array($type, ['loan_disbursement', 'loan_repayment']) ? Loan::factory() : null,
            'related_account_id' => $type === 'transfer' ? Account::factory() : null,
            'reversal_reason' => null,
            'reversed_by' => null,
            'reversed_at' => null,
            'processed_by' => User::factory(),
            'metadata' => null,
        ];
    }
}
