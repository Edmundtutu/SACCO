<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Account;
use App\Models\Transaction;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $members = User::query()->where('role', 'member')->inRandomOrder()->take(20)->get();
        foreach ($members as $member) {
            $accounts = Account::query()->where('member_id', $member->id)->get();
            if ($accounts->isEmpty()) {
                $account = Account::factory()->create(['member_id' => $member->id]);
                $accounts = collect([$account]);
            }

            foreach ($accounts as $account) {
                Transaction::factory()->count(rand(5, 20))->create([
                    'member_id' => $member->id,
                    'account_id' => $account->id,
                ]);
            }
        }
    }
}
