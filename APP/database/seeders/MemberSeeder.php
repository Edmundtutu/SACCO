<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use App\Models\Loan;
use App\Models\Transaction;
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
        // Create batches of users with role=member and attach related data
        User::factory()
            ->count(35)
            ->state(fn () => ['role' => 'member', 'status' => 'active'])
            ->create()
            ->each(function (User $user) {
                Account::factory()->count(6)->create(['member_id' => $user->id]);
                Transaction::factory()->count(20)->create(['member_id' => $user->id]);
                Loan::factory()->count(3)->create(['member_id' => $user->id]);
            });

        User::factory()
            ->count(25)
            ->state(fn () => ['role' => 'member', 'status' => 'active'])
            ->create()
            ->each(function (User $user) {
                Account::factory()->count(1)->create(['member_id' => $user->id]);
                Transaction::factory()->count(15)->create(['member_id' => $user->id]);
                Loan::factory()->count(2)->create(['member_id' => $user->id]);
            });

        User::factory()
            ->count(5)
            ->state(fn () => ['role' => 'member', 'status' => 'active'])
            ->create()
            ->each(function (User $user) {
                Account::factory()->count(1)->create(['member_id' => $user->id]);
            });

        User::factory()
            ->count(5)
            ->state(fn () => ['role' => 'member', 'status' => 'active'])
            ->create()
            ->each(function (User $user) {
                Account::factory()->count(2)->create(['member_id' => $user->id]);
                Transaction::factory()->count(12)->create(['member_id' => $user->id]);
                Loan::factory()->count(1)->create(['member_id' => $user->id]);
            });

        User::factory()
            ->count(3)
            ->state(fn () => ['role' => 'member', 'status' => 'active'])
            ->create()
            ->each(function (User $user) {
                Account::factory()->count(1)->create(['member_id' => $user->id]);
                Transaction::factory()->count(3)->create(['member_id' => $user->id]);
            });

        User::factory()
            ->count(1)
            ->state(fn () => ['role' => 'member', 'status' => 'active'])
            ->create()
            ->each(function (User $user) {
                Account::factory()->count(1)->create(['member_id' => $user->id]);
                Transaction::factory()->count(1)->create(['member_id' => $user->id]);
                Loan::factory()->count(1)->create(['member_id' => $user->id]);
            });
    }
}
