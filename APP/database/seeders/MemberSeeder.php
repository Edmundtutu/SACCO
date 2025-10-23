<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanAccount;
use App\Models\Transaction;
use App\Models\Share;
use App\Models\ShareAccount;
use App\Models\LoanGuarantor;
use App\Models\LoanRepayment;
use App\Models\Membership\Membership;
use App\Models\Membership\IndividualProfile;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create staff users first for processing transactions
        $staffUsers = User::factory()
            ->count(5)
            ->state(fn () => ['role' => 'staff_level_1', 'status' => 'active'])
            ->create();

        // Create batches of users with role=member and attach related data
        User::factory()
            ->count(20)
            ->state(fn () => ['role' => 'member', 'status' => 'active'])
            ->create()
            ->each(function (User $user) use ($staffUsers) {
                // Create individual profile and membership
                $profile = IndividualProfile::factory()->create();
                Membership::factory()->approved()->create([
                    'user_id' => $user->id,
                    'profile_type' => IndividualProfile::class,
                    'profile_id' => $profile->id,
                ]);

                // Create accounts
                $accounts = Account::factory()->count(2)->create(['member_id' => $user->id]);
                
                // Create transactions for each account
                foreach ($accounts as $account) {
                    Transaction::factory()->count(10)->create([
                        'member_id' => $user->id,
                        'account_id' => $account->id,
                        'processed_by' => $staffUsers->random()->id,
                    ]);
                }

                // Create loan account with loans
                $loanAccount = LoanAccount::factory()->fresh()->create();
                Account::create([
                    'account_number' => 'LN' . str_pad($user->id, 8, '0', STR_PAD_LEFT),
                    'member_id' => $user->id,
                    'accountable_type' => LoanAccount::class,
                    'accountable_id' => $loanAccount->id,
                    'status' => 'active',
                ]);
                
                $loans = Loan::factory()->count(2)->create([
                    'member_id' => $user->id,
                    'loan_account_id' => $loanAccount->id,
                ]);
                
                // Create guarantors and repayments for loans
                foreach ($loans as $loan) {
                    LoanGuarantor::factory()->count(2)->create(['loan_id' => $loan->id]);
                    LoanRepayment::factory()->count(5)->create(['loan_id' => $loan->id]);
                }

                // Create share account with certificates
                $shareAccount = ShareAccount::factory()->fresh()->create();
                Account::create([
                    'account_number' => 'SHR' . str_pad($user->id, 8, '0', STR_PAD_LEFT),
                    'member_id' => $user->id,
                    'accountable_type' => ShareAccount::class,
                    'accountable_id' => $shareAccount->id,
                    'status' => 'active',
                ]);
                
                Share::factory()->count(3)->create([
                    'member_id' => $user->id,
                    'share_account_id' => $shareAccount->id,
                ]);
            });

        User::factory()
            ->count(15)
            ->state(fn () => ['role' => 'member', 'status' => 'active'])
            ->create()
            ->each(function (User $user) use ($staffUsers) {
                // Create individual profile and membership
                $profile = IndividualProfile::factory()->create();
                Membership::factory()->approved()->create([
                    'user_id' => $user->id,
                    'profile_type' => IndividualProfile::class,
                    'profile_id' => $profile->id,
                ]);

                // Create accounts
                $accounts = Account::factory()->count(1)->create(['member_id' => $user->id]);
                
                // Create transactions
                foreach ($accounts as $account) {
                    Transaction::factory()->count(8)->create([
                        'member_id' => $user->id,
                        'account_id' => $account->id,
                        'processed_by' => $staffUsers->random()->id,
                    ]);
                }

                // Create loan account with loans
                $loanAccount = LoanAccount::factory()->fresh()->create();
                Account::create([
                    'account_number' => 'LN' . str_pad($user->id, 8, '0', STR_PAD_LEFT),
                    'member_id' => $user->id,
                    'accountable_type' => LoanAccount::class,
                    'accountable_id' => $loanAccount->id,
                    'status' => 'active',
                ]);
                
                $loans = Loan::factory()->count(1)->create([
                    'member_id' => $user->id,
                    'loan_account_id' => $loanAccount->id,
                ]);
                
                foreach ($loans as $loan) {
                    LoanGuarantor::factory()->count(1)->create(['loan_id' => $loan->id]);
                    LoanRepayment::factory()->count(3)->create(['loan_id' => $loan->id]);
                }

                // Create share account with certificates
                $shareAccount = ShareAccount::factory()->fresh()->create();
                Account::create([
                    'account_number' => 'SHR' . str_pad($user->id, 8, '0', STR_PAD_LEFT),
                    'member_id' => $user->id,
                    'accountable_type' => ShareAccount::class,
                    'accountable_id' => $shareAccount->id,
                    'status' => 'active',
                ]);
                
                Share::factory()->count(2)->create([
                    'member_id' => $user->id,
                    'share_account_id' => $shareAccount->id,
                ]);
            });

        // Create some members with minimal data
        User::factory()
            ->count(5)
            ->state(fn () => ['role' => 'member', 'status' => 'active'])
            ->create()
            ->each(function (User $user) {
                // Create individual profile and membership
                $profile = IndividualProfile::factory()->create();
                Membership::factory()->approved()->create([
                    'user_id' => $user->id,
                    'profile_type' => IndividualProfile::class,
                    'profile_id' => $profile->id,
                ]);

                // Create minimal accounts and transactions
                $account = Account::factory()->create(['member_id' => $user->id]);
                Transaction::factory()->count(3)->create([
                    'member_id' => $user->id,
                    'account_id' => $account->id,
                ]);

                // Create share account with certificate
                $shareAccount = ShareAccount::factory()->fresh()->create();
                Account::create([
                    'account_number' => 'SHR' . str_pad($user->id, 8, '0', STR_PAD_LEFT),
                    'member_id' => $user->id,
                    'accountable_type' => ShareAccount::class,
                    'accountable_id' => $shareAccount->id,
                    'status' => 'active',
                ]);
                
                Share::factory()->count(1)->create([
                    'member_id' => $user->id,
                    'share_account_id' => $shareAccount->id,
                ]);
            });
    }
}
