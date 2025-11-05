<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\LoanAccount;
use App\Models\SavingsAccount;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MemberLoansAccountsSeeder extends Seeder
{
    /**
     * Seed a LoanAccount (and parent Account) for each existing member.
     * Links to the member's wallet savings account if present.
     *
     * @return void
     */
    public function run()
    {
        $members = User::where('role', 'member')->get();

        if ($members->isEmpty()) {
            $this->command->warn('⚠️ No members found.');
            return;
        }

        $createdCount = 0;

        foreach ($members as $member) {
            // Skip if member already has a loan account
            $existingLoanAccount = Account::where('member_id', $member->id)
                ->where('accountable_type', LoanAccount::class)
                ->first();

            if ($existingLoanAccount) {
                continue;
            }

            DB::beginTransaction();
            try {
                // Try link to a wallet savings account if available
                $walletAccount = Account::walletAccounts()
                    ->where('member_id', $member->id)
                    ->first();

                $linkedSavingsAccountId = $walletAccount?->accountable_id; // id in savings_accounts table

                // Create the accountable LoanAccount with sensible defaults
                $loanAccount = LoanAccount::create([
                    'total_disbursed_amount' => 0,
                    'total_repaid_amount' => 0,
                    'current_outstanding' => 0,
                    'linked_savings_account' => $linkedSavingsAccountId,
                    'min_loan_limit' => 0,
                    'max_loan_limit' => null,
                    'repayment_frequency_type' => 'monthly',
                    'status_notes' => null,
                    'last_activity_date' => now(),
                    'account_features' => [],
                    'audit_trail' => [
                        'created_by' => 'seeder',
                        'created_at' => now()->toDateTimeString(),
                    ],
                    'remarks' => 'Auto-created by MemberLoansAccountsSeeder',
                ]);

                // Create the parent Account linking the member and the LoanAccount
                Account::create([
                    'member_id' => $member->id,
                    'accountable_type' => LoanAccount::class,
                    'accountable_id' => $loanAccount->id,
                    'status' => 'active',
                ]);

                DB::commit();
                $createdCount++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->command->error("❌ Failed creating loan account for member ID {$member->id}: {$e->getMessage()}");
            }
        }

        $this->command->info("✅ Loan accounts created successfully for {$createdCount} members.");
    }
}
