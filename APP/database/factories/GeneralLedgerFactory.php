<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GeneralLedger>
 */
class GeneralLedgerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $debitAmount = $this->faker->randomFloat(2, 100, 10000);
        $creditAmount = $this->faker->randomFloat(2, 100, 10000);
        
        return [
            'transaction_id' => 'TXN' . $this->faker->unique()->numerify('##########'),
            'transaction_date' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'account_code' => $this->faker->randomElement(['1001', '1002', '2001', '2002', '3001', '3002']),
            'account_name' => $this->faker->randomElement([
                'Cash in Hand',
                'Bank Account',
                'Member Savings',
                'Loan Portfolio',
                'Interest Income',
                'Service Fees'
            ]),
            'account_type' => $this->faker->randomElement(['asset', 'liability', 'equity', 'income', 'expense']),
            'description' => $this->faker->sentence(),
            'reference_type' => $this->faker->randomElement(['Transaction', 'Loan', 'Deposit']),
            'reference_id' => $this->faker->numberBetween(1, 100),
            'debit_amount' => $debitAmount,
            'credit_amount' => $creditAmount,
            'status' => 'posted',
            'posted_by' => 1, // Assuming user ID 1 exists
            'posted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}