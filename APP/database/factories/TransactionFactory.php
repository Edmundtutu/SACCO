<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\User;
use App\Models\Account;
use App\Models\SavingsAccount;
use App\Models\LoanAccount;
use App\Models\ShareAccount;
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
        // Get existing users and accounts, or return null if none exist
        $user = User::inRandomOrder()->first();
        $account = Account::with('accountable')->inRandomOrder()->first();

        // If no existing data, fall back to creating new records
        if (!$user) {
            $user = User::factory()->create();
        }
        if (!$account) {
            $account = Account::factory()->create();
        }

        // Determine transaction type based on account's accountable_type
        $accountableType = $account->accountable_type ?? null;

        // Map transaction types to account types
        if ($accountableType === SavingsAccount::class) {
            $type = $this->faker->randomElement([
                'deposit', 'withdrawal', 'transfer', 'interest', 'fee'
            ]);
            $category = 'savings';
        } elseif ($accountableType === LoanAccount::class) {
            $type = $this->faker->randomElement([
                'loan_disbursement', 'loan_repayment', 'interest', 'fee'
            ]);
            $category = 'loan';
        } elseif ($accountableType === ShareAccount::class) {
            $type = $this->faker->randomElement([
                'share_purchase', 'share_redemption', 'dividend', 'fee'
            ]);
            $category = 'share';
        } else {
            // Fallback for unknown types
            $type = $this->faker->randomElement([
                'deposit', 'withdrawal', 'transfer', 'fee'
            ]);
            $category = 'administrative';
        }

        $amount = $this->faker->randomFloat(2, 10, 20000);
        $fee = in_array($type, ['withdrawal', 'transfer', 'loan_disbursement', 'loan_repayment', 'fee'])
            ? $this->faker->randomFloat(2, 0, min(500, $amount * 0.05))
            : 0;
        $net = $amount - $fee;

        // Get existing related records when needed
        $relatedLoanId = null;
        if (in_array($type, ['loan_disbursement', 'loan_repayment'])) {
            $relatedLoan = Loan::inRandomOrder()->first();
            $relatedLoanId = $relatedLoan ? $relatedLoan->id : Loan::factory()->create()->id;
        }

        $relatedAccountId = null;
        if ($type === 'transfer') {
            $relatedAccount = Account::where('id', '!=', $account->id)->inRandomOrder()->first();
            $relatedAccountId = $relatedAccount ? $relatedAccount->id : null;
        }

        $processedBy = User::inRandomOrder()->first();
        if (!$processedBy) {
            $processedBy = $user;
        }

        return [
            'transaction_number' => 'TXN' . str_pad((string)$this->faker->unique()->numberBetween(1, 9999999999), 10, '0', STR_PAD_LEFT),
            'member_id' => $user->id,
            'account_id' => $account->id,
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
            'related_loan_id' => $relatedLoanId,
            'related_account_id' => $relatedAccountId,
            'reversal_reason' => null,
            'reversed_by' => null,
            'reversed_at' => null,
            'processed_by' => $processedBy->id,
            'metadata' => null,
        ];
    }
}
