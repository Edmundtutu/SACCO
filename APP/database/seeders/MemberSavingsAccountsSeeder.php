<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use App\Models\SavingsAccount;
use App\Models\SavingsProduct;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MemberSavingsAccountsSeeder extends Seeder
{
    /**
     * This Seeder class is a demonstration of how to create wallet accounts for the existing Members:
     *
     * @return void
     */
    public function run()
    {
        $wallet = SavingsProduct::where(['type' => 'special', 'code' => 'WL001'])->first(); // wallet produt
        $members = User::where('role', 'member')->get();

        if (!$wallet || !$members) {
            $this->command->error('❌Wallet savings product not found.');
            return;
        }

        // create a wallet for each member
        foreach ($members as $member) {
            // first create accountable type of the savings account of the product type wallet
            $accountable = SavingsAccount::create([
                'savings_product_id' => $wallet->id,
                'balance' => 0,
                'available_balance' => 0,
                'minimum_balance' => 0,
                'interest_earned' => 0,
                'interest_rate' => 0,
                'last_interest_calculation' => now(),
                'maturity_date' => null,
            ]);
            $account = Account::create([
                'member_id' => $member->id,
                'accountable_type' => 'App\Models\SavingsAccount',
                'accountable_id' => $accountable->id,
                'status' => 'active',
            ]);
        }
        $this->command->info('✅Wallet savings accounts created successfully.');
    }
}
