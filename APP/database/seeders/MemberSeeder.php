<?php

namespace Database\Seeders;

use App\Models\Member;
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
        Member::factory()
            ->count(35)
            ->hasAccounts(6)
            ->hasTransactions(20)
            ->hasLoans(3)
            ->create();
        
        Member::factory()
            ->count(25)
            ->hasAccounts(1)
            ->hasTransactions(15)
            ->hasLoans(2)
            ->create();


        Member::factory()
            ->count(5)
            ->hasAccounts(1)
            ->create();

        Member::factory()
            ->count(5)
            ->hasAccounts(2)
            ->hasTransactions(12)
            ->hasLoans(1)
            ->create();

        Member::factory()
            ->count(3)
            ->hasAccounts(1)
            ->hasTransactions(3)
             ->create();

        Member::factory()
            ->count(1)
            ->hasAccounts(1)
            ->hasTransactions(1)
            ->hasLoans(1)
            ->create();
    }
}
