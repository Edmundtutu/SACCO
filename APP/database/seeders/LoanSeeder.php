<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Loan;
use App\Models\LoanAccount;
use App\Models\Account;

class LoanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Creates LoanAccounts with linked individual Loans
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Seeding loans with loan accounts...');
        
        $members = User::query()->where('role', 'member')->inRandomOrder()->take(20)->get();
        
        foreach ($members as $member) {
            // 60% chance of having a loan account
            if (rand(0, 9) < 6) {
                // Create loan account (aggregate tracker)
                $loanAccount = LoanAccount::factory()->fresh()->create();
                
                // Create polymorphic account link
                $account = Account::create([
                    'account_number' => 'LN' . str_pad($member->id, 8, '0', STR_PAD_LEFT),
                    'member_id' => $member->id,
                    'accountable_type' => LoanAccount::class,
                    'accountable_id' => $loanAccount->id,
                    'status' => 'active',
                    'opening_date' => now()->subMonths(rand(6, 36)),
                ]);
                
                // Create 0-3 individual loan applications
                $loanCount = rand(0, 3);
                $totalDisbursed = 0;
                $totalRepaid = 0;
                $currentOutstanding = 0;
                
                for ($i = 0; $i < $loanCount; $i++) {
                    $loan = Loan::factory()->create([
                        'member_id' => $member->id,
                        'loan_account_id' => $loanAccount->id,
                    ]);
                    
                    // Update aggregates based on loan status
                    if (in_array($loan->status, ['disbursed', 'active', 'completed'])) {
                        $totalDisbursed += $loan->principal_amount;
                        $totalRepaid += $loan->total_paid;
                    }
                    
                    if (in_array($loan->status, ['disbursed', 'active'])) {
                        $currentOutstanding += $loan->outstanding_balance;
                    }
                }
                
                // Update loan account with calculated totals
                if ($loanCount > 0) {
                    $loanAccount->update([
                        'total_disbursed_amount' => $totalDisbursed,
                        'total_repaid_amount' => $totalRepaid,
                        'current_outstanding' => $currentOutstanding,
                        'last_activity_date' => now()->subDays(rand(1, 30)),
                    ]);
                }
                
                $this->command->info("  Created loan account for {$member->name} with {$loanCount} loans");
            }
        }
        
        $this->command->info('✅ Loan seeding completed!');
        $this->command->info('Total loan accounts: ' . Account::where('accountable_type', LoanAccount::class)->count());
        $this->command->info('Total individual loans: ' . Loan::count());
    }
}
