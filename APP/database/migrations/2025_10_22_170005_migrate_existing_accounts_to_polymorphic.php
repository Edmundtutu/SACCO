<?php

use App\Models\Account;
use App\Models\SavingsAccount;
use App\Models\SavingsProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;
use Nette\Utils\Random;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Migrate existing savings-based accounts to the new polymorphic structure
     */
    public function up(): void
    {
        // This migration assumes you're starting fresh or have backed up your data
        // If you have existing accounts, this will migrate them to savings_accounts

        $existingAccounts = DB::table('accounts_backup')->get();

        // For damage control, let's create a random selection of savings_product between 1, 2, 3 
        // => representing Compulsory, Voluntary, and Fixed Deposit savings.

        $compulsorySavings = SavingsProduct::where('type', 'compulsory')->first();
        $voluntarySavings = SavingsProduct::where('type', 'voluntary')->first();
        $fixedDeposit = SavingsProduct::where('type', 'fixed_deposit')->first();

        // Choose a random product among the three above
        $products = [$compulsorySavings, $voluntarySavings, $fixedDeposit];

        // Generate random balances
        $balance = rand(5000, 50000);
        $available = max(0, $balance - rand(0, 1000));

        foreach ($existingAccounts as $oldAccount) {
            // randomize the product first
            $old_saving_product_id_damage_controlled = $products[array_rand($products)];

            // Create new savings account record
            $savingsAccountId = DB::table('savings_accounts')->insertGetId([
                'savings_product_id'   => $old_saving_product_id_damage_controlled->id,
                'balance'              => $balance,
                'available_balance'    => $available,
                'minimum_balance'      => $old_saving_product_id_damage_controlled->minimum_balance,
                'interest_earned'      => 0,
                'created_at'           => $oldAccount->created_at,
                'updated_at'           => $oldAccount->updated_at,
            ]);

            // Update the old account to point to the new savings account
            DB::table('accounts')
                ->where('id', $oldAccount->id)
                ->update([
                    'accountable_type' => \App\Models\SavingsAccount::class, // Adjust namespace if different
                    'accountable_id'   => $savingsAccountId,
                ]);
        }


        // Note: Loans and Shares tables already exist, so we'll need separate migrations
        // to convert them to loan_accounts and share_accounts if needed
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a destructive operation - only use if you're sure!
        // Restore data from backup table

        $accounts = DB::table('accounts')->get();

        foreach ($accounts as $account) {
            if ($account->accountable_type === SavingsAccount::class) {
                $savingsAccount = DB::table('savings_accounts')
                    ->where('id', $account->accountable_id)
                    ->first();

                if ($savingsAccount) {
                    DB::table('accounts')
                        ->where('id', $account->id)
                        ->update([
                            'savings_product_id' => $savingsAccount->savings_product_id,
                            'balance' => $savingsAccount->balance,
                            'available_balance' => $savingsAccount->available_balance,
                            'minimum_balance' => $savingsAccount->minimum_balance,
                            'interest_earned' => $savingsAccount->interest_earned,
                            'last_interest_calculation' => $savingsAccount->last_interest_calculation,
                            'maturity_date' => $savingsAccount->maturity_date,
                        ]);
                }
            }
        }
    }
};
