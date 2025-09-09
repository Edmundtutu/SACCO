<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Loan;

class LoanSeeder extends Seeder
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
            Loan::factory()->count(rand(0, 2))->create(['member_id' => $member->id]);
        }
    }
}
