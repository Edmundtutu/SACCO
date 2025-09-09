<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Account;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create accounts for a few random members
        $members = User::query()->where('role', 'member')->inRandomOrder()->take(10)->get();
        foreach ($members as $member) {
            Account::factory()->count(rand(1, 3))->create(['member_id' => $member->id]);
        }
    }
}
