<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\Account;
use App\Models\SavingsAccount;

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
        
        foreach ($existingAccounts as $oldAccount) {
            // Create new savings account record
            $savingsAccountId = DB::table('savings_accounts')->insertGetId([
                'savings_product_id' => $oldAccount->savings_product_id,
                'balance' => $oldAccount->balance,
                'available_balance' => $oldAccount->available_balance,
                'minimum_balance' => $oldAccount->minimum_balance,
                'interest_earned' => $oldAccount->interest_earned,
                'interest_rate' => 0, // You may want to pull this from savings_product
                'last_interest_calculation' => $oldAccount->last_interest_calculation,
                'maturity_date' => $oldAccount->maturity_date,
                'last_transaction_date' => $oldAccount->last_transaction_date,
                'created_at' => $oldAccount->created_at,
                'updated_at' => $oldAccount->updated_at,
            ]);
            
            // Update the account to point to the savings account
            DB::table('accounts')
                ->where('id', $oldAccount->id)
                ->update([
                    'accountable_type' => SavingsAccount::class,
                    'accountable_id' => $savingsAccountId,
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
                            'last_transaction_date' => $savingsAccount->last_transaction_date,
                        ]);
                }
            }
        }
    }
};
