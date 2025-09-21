<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use App\Models\Loan;
use App\Models\Transaction;
use App\Models\Share;
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

                // Create loans
                $loans = Loan::factory()->count(2)->create(['member_id' => $user->id]);
                
                // Create guarantors for loans
                foreach ($loans as $loan) {
                    LoanGuarantor::factory()->count(2)->create(['loan_id' => $loan->id]);
                    LoanRepayment::factory()->count(5)->create(['loan_id' => $loan->id]);
                }

                // Create shares
                Share::factory()->count(3)->create(['member_id' => $user->id]);
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

                // Create loans
                $loans = Loan::factory()->count(1)->create(['member_id' => $user->id]);
                foreach ($loans as $loan) {
                    LoanGuarantor::factory()->count(1)->create(['loan_id' => $loan->id]);
                    LoanRepayment::factory()->count(3)->create(['loan_id' => $loan->id]);
                }

                // Create shares
                Share::factory()->count(2)->create(['member_id' => $user->id]);
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

                // Create shares
                Share::factory()->count(1)->create(['member_id' => $user->id]);
            });
    }
}
