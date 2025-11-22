<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use App\Models\SavingsAccount;
use App\Models\SavingsProduct;
use App\Models\LoanAccount;
use App\Models\ShareAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * UniversalAccountSeeder - Creates all account types for existing members
 * Creates: Wallet accounts, Savings accounts, Loan accounts, Share accounts
 * Uses ::class for polymorphic references, DB transactions for atomicity
 * Idempotent: checks for existing accounts before creating
 */
class UniversalAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🔄 Starting UniversalAccountSeeder...');

        // Get wallet product (must exist from WalletProductSeeder)
        $walletProduct = SavingsProduct::where('type', 'wallet')->first();
        if (!$walletProduct) {
            $this->command->error('❌ Wallet product (WL001) not found! Run WalletProductSeeder first.');
            return;
        }

        // Get other savings products
        $compulsorySavings = SavingsProduct::where('type', 'compulsory')->first();
        $voluntarySavings = SavingsProduct::where('type', 'voluntary')->first();

        // Get all members
        $members = User::where('role', 'member')->get();
        $this->command->info('📋 Found ' . $members->count() . ' members to process');

        $accountsCreated = [
            'wallet' => 0,
            'savings' => 0,
            'loan' => 0,
            'share' => 0,
        ];

        foreach ($members as $index => $member) {
            DB::transaction(function () use ($member, $walletProduct, $compulsorySavings, $voluntarySavings, &$accountsCreated) {

                // 1. Create wallet account for every member (if not exists)
                $existingWallet = Account::where('member_id', $member->id)
                    ->whereHasMorph('accountable', [SavingsAccount::class], function($q) use ($walletProduct) {
                        $q->where('savings_product_id', $walletProduct->id);
                    })
                    ->first();

                if (!$existingWallet) {
                    $walletSavingsAccount = SavingsAccount::create([
                        'savings_product_id' => $walletProduct->id,
                        'balance' => 0,
                        'available_balance' => 0,
                        'minimum_balance' => 0,
                        'interest_earned' => 0,
                        'interest_rate' => 0,
                    ]);

                    Account::create([
                        'member_id' => $member->id,
                        'accountable_type' => SavingsAccount::class,
                        'accountable_id' => $walletSavingsAccount->id,
                        'status' => 'active',

                    ]);

                    $accountsCreated['wallet']++;
                }

                // 2. Create compulsory savings account (70% of members)
                if ($compulsorySavings && rand(1, 100) <= 70) {
                    $existingCompulsory = Account::where('member_id', $member->id)
                        ->whereHasMorph('accountable', [SavingsAccount::class], function($q) use ($compulsorySavings) {
                            $q->where('savings_product_id', $compulsorySavings->id);
                        })
                        ->first();

                    if (!$existingCompulsory) {
                        $balance = rand(1000, 50000);
                        $compulsorySavingsAccount = SavingsAccount::create([
                            'savings_product_id' => $compulsorySavings->id,
                            'balance' => $balance,
                            'available_balance' => $balance,
                            'minimum_balance' => $compulsorySavings->minimum_balance,
                            'interest_earned' => rand(0, 1000),
                            'interest_rate' => $compulsorySavings->interest_rate,
                        ]);

                        Account::create([
                            'member_id' => $member->id,
                            'accountable_type' => SavingsAccount::class,
                            'accountable_id' => $compulsorySavingsAccount->id,
                            'status' => 'active',

                        ]);

                        $accountsCreated['savings']++;
                    }
                }

                // 3. Create voluntary savings account (40% of members)
                if ($voluntarySavings && rand(1, 100) <= 40) {
                    $existingVoluntary = Account::where('member_id', $member->id)
                        ->whereHasMorph('accountable', [SavingsAccount::class], function($q) use ($voluntarySavings) {
                            $q->where('savings_product_id', $voluntarySavings->id);
                        })
                        ->first();

                    if (!$existingVoluntary) {
                        $balance = rand(500, 20000);
                        $voluntarySavingsAccount = SavingsAccount::create([
                            'savings_product_id' => $voluntarySavings->id,
                            'balance' => $balance,
                            'available_balance' => $balance,
                            'minimum_balance' => $voluntarySavings->minimum_balance,
                            'interest_earned' => rand(0, 500),
                            'interest_rate' => $voluntarySavings->interest_rate,
                        ]);

                        Account::create([
                            'member_id' => $member->id,
                            'accountable_type' => SavingsAccount::class,
                            'accountable_id' => $voluntarySavingsAccount->id,
                            'status' => 'active',

                        ]);

                        $accountsCreated['savings']++;
                    }
                }

                // 4. Create loan account (60% of members)
                if (rand(1, 100) <= 60) {
                    $existingLoanAccount = Account::where('member_id', $member->id)
                        ->where('accountable_type', LoanAccount::class)
                        ->first();

                    if (!$existingLoanAccount) {
                        $loanAccount = LoanAccount::factory()->create();

                        Account::create([
                            'member_id' => $member->id,
                            'accountable_type' => LoanAccount::class,
                            'accountable_id' => $loanAccount->id,
                            'status' => 'active',
                        ]);

                        $accountsCreated['loan']++;
                    }
                }

                // 5. Create share account (80% of members)
                if (rand(1, 100) <= 80) {
                    $existingShareAccount = Account::where('member_id', $member->id)
                        ->where('accountable_type', ShareAccount::class)
                        ->first();

                    if (!$existingShareAccount) {
                        $shareAccount = ShareAccount::factory()->fresh()->create();

                        Account::create([
                            'member_id' => $member->id,
                            'accountable_type' => ShareAccount::class,
                            'accountable_id' => $shareAccount->id,
                            'status' => 'active',

                        ]);

                        $accountsCreated['share']++;
                    }
                }
            });

            if ((($index + 1) % 10) == 0) {
                $this->command->info('  Processed ' . ($index + 1) . '/' . $members->count() . ' members...');
            }
        }

        $this->command->info('✅ UniversalAccountSeeder complete!');
        $this->command->info('   - Wallets: ' . $accountsCreated['wallet']);
        $this->command->info('   - Savings: ' . $accountsCreated['savings']);
        $this->command->info('   - Loans: ' . $accountsCreated['loan']);
        $this->command->info('   - Shares: ' . $accountsCreated['share']);
    }
}
