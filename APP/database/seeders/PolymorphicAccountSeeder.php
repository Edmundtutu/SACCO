<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\SavingsAccount;
use App\Models\LoanAccount;
use App\Models\ShareAccount;
use App\Models\User;
use App\Models\SavingsProduct;
use App\Models\LoanProduct;
use Illuminate\Database\Seeder;

class PolymorphicAccountSeeder extends Seeder
{
    /**
     * Seed polymorphic account structure with test data
     */
    public function run(): void
    {
        $this->command->info('Seeding polymorphic accounts...');
        
        // Get or create test members
        $members = User::where('role', 'member')->take(10)->get();
        
        if ($members->isEmpty()) {
            $this->command->warn('No members found. Creating test members...');
            $members = User::factory()->count(10)->create(['role' => 'member']);
        }
        
        foreach ($members as $member) {
            // Create 1-2 savings accounts per member
            $savingsCount = rand(1, 2);
            for ($i = 0; $i < $savingsCount; $i++) {
                $savingsAccount = SavingsAccount::factory()->create();
                
                Account::create([
                    'account_number' => 'ACC' . str_pad((string)rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'member_id' => $member->id,
                    'accountable_type' => SavingsAccount::class,
                    'accountable_id' => $savingsAccount->id,
                    'status' => 'active',
                ]);
            }
            
            // 50% chance of having a loan account
            if (rand(0, 1)) {
                $loanAccount = LoanAccount::factory()->create();
                
                Account::create([
                    'account_number' => 'LN' . str_pad((string)rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'member_id' => $member->id,
                    'accountable_type' => LoanAccount::class,
                    'accountable_id' => $loanAccount->id,
                    'status' => 'active',
                ]);
            }
            
            // 70% chance of having shares
            if (rand(0, 9) < 7) {
                $shareAccount = ShareAccount::factory()->create();
                
                Account::create([
                    'account_number' => 'SHR' . str_pad((string)rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'member_id' => $member->id,
                    'accountable_type' => ShareAccount::class,
                    'accountable_id' => $shareAccount->id,
                    'status' => 'active',
                ]);
            }
        }
        
        $this->command->info('Polymorphic accounts seeded successfully!');
        $this->command->info('Created accounts breakdown:');
        $this->command->info('- Savings: ' . Account::where('accountable_type', SavingsAccount::class)->count());
        $this->command->info('- Loans: ' . Account::where('accountable_type', LoanAccount::class)->count());
        $this->command->info('- Shares: ' . Account::where('accountable_type', ShareAccount::class)->count());
    }
}
