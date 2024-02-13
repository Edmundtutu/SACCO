<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\Member;
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
        $type = $this->faker->randomElement(['Deposit','Withdraw','LoanRepay']);
        
        return [
            'transaction_type'=>$type,
            'amount'=> $this->faker->numberBetween(1,6000),
            'Date_of_transaction' => $this->faker->dateTimeThisDecade(),
            'member_id'=> Member::factory(),
            'account_id'=> Account::factory(),
            'loan_id' => $type == 'LoanRepay'? Loan::factory() : NULL
        ];
    }
}
