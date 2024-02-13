<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Account;
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
        $loantype = $this->faker->randomElement(['Personal','Team','Bussiness']);
        $loanamount = $this->faker->numberBetween(100,30000);
        $intrestrate = NuLL;
        // to determine the intrest rate according to the loanamount
        if($loanamount<=500){
            $intrestrate = 0.0;
        }elseif ($loanamount<=1000) {
            $intrestrate = 0.2;   
        }elseif ($loanamount<=10000) {
            $intrestrate = 0.5;      
        }else{
            $intrestrate = 0.7;
        }

        // $intrestrate = $faker->randomFloat(0.2,0.5,0.7); // the intrest rate varies according to the loan amount
        $loanstatus= $this->faker-> randomElement(['Paid', 'Active', 'Defaulted']); // can be Paid, active , or Defaulted(givenup on)
 
        return [
            'loan_type' => $loantype,
            'DOA' => $this->faker->dateTimeThisDecade(),
            'DOR' => $loanstatus == 'Paid' ? $this->faker->dateTimeThisDecade():NULL,
            'loan_amount' => $loanamount,
            'loan_status' =>$loanstatus,
            'repayment_terms' => $this->faker->sentence(),
            'intrest_rate' => $intrestrate,
            'member_id' => Member :: factory(),
            'account_id' => Account::factory()
        ];
    }
}
