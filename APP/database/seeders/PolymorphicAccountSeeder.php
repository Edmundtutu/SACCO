<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\SavingsAccount;
use App\Models\LoanAccount;
use App\Models\ShareAccount;
use App\Models\Loan;
use App\Models\Share;
use App\Models\User;
use App\Models\SavingsProduct;
use App\Models\LoanProduct;
use Illuminate\Database\Seeder;

class PolymorphicAccountSeeder extends Seeder
{
    /**
     * Seed polymorphic account structure with test data
     * Creates accounts with linked individual loans and share certificates
     */
    public function run(): void
    {
        $this->command->info('Seeding polymorphic accounts with entities...');
        
        // Get or create test members
        $members = User::where('role', 'member')->take(10)->get();
        
        if ($members->isEmpty()) {
            $this->command->warn('No members found. Creating test members...');
            $members = User::factory()->count(10)->create(['role' => 'member']);
        }
        
        $totalLoans = 0;
        $totalShareCertificates = 0;
        
        foreach ($members as $member) {
            $this->command->info("Seeding accounts for member: {$member->name}");
            
            // 1. Create Savings Account(s)
            $savingsCount = rand(1, 2);
            for ($i = 0; $i < $savingsCount; $i++) {
                $savingsAccount = SavingsAccount::factory()->create();
                
                Account::create([
                    'account_number' => 'SAV' . str_pad((string)rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                    'member_id' => $member->id,
                    'accountable_type' => SavingsAccount::class,
                    'accountable_id' => $savingsAccount->id,
                    'status' => 'active',
                    'opening_date' => now()->subMonths(rand(6, 36)),
                ]);
            }
            
            // 2. Create Loan Account with individual loans (60% chance)
            if (rand(0, 9) < 6) {
                // Create loan account (aggregate tracker)
                $loanAccount = LoanAccount::factory()->fresh()->create();
                
                $account = Account::create([
                    'account_number' => 'LN' . str_pad($member->id, 8, '0', STR_PAD_LEFT),
                    'member_id' => $member->id,
                    'accountable_type' => LoanAccount::class,
                    'accountable_id' => $loanAccount->id,
                    'status' => 'active',
                    'opening_date' => now()->subMonths(rand(12, 48)),
                ]);
                
                // Create 1-3 individual loan applications
                $loanCount = rand(1, 3);
                $totalDisbursed = 0;
                $totalRepaid = 0;
                $currentOutstanding = 0;
                
                for ($j = 0; $j < $loanCount; $j++) {
                    $loanProduct = LoanProduct::inRandomOrder()->first() ?? LoanProduct::factory()->create();
                    
                    $loan = Loan::factory()->create([
                        'member_id' => $member->id,
                        'loan_account_id' => $loanAccount->id,
                        'loan_product_id' => $loanProduct->id,
                        'status' => $this->faker()->randomElement(['disbursed', 'active', 'completed']),
                    ]);
                    
                    $totalLoans++;
                    
                    // Update aggregates
                    if (in_array($loan->status, ['disbursed', 'active', 'completed'])) {
                        $totalDisbursed += $loan->principal_amount;
                        $totalRepaid += $loan->total_paid;
                    }
                    
                    if (in_array($loan->status, ['disbursed', 'active'])) {
                        $currentOutstanding += $loan->outstanding_balance;
                    }
                }
                
                // Update loan account with calculated totals
                $loanAccount->update([
                    'total_disbursed_amount' => $totalDisbursed,
                    'total_repaid_amount' => $totalRepaid,
                    'current_outstanding' => $currentOutstanding,
                    'last_activity_date' => now()->subDays(rand(1, 30)),
                ]);
                
                $this->command->info("  → Created loan account with {$loanCount} loans");
            }
            
            // 3. Create Share Account with individual certificates (70% chance)
            if (rand(0, 9) < 7) {
                // Create share account (aggregate tracker)
                $shareAccount = ShareAccount::factory()->fresh()->create([
                    'share_price' => 1000, // Current price
                ]);
                
                $account = Account::create([
                    'account_number' => 'SHR' . str_pad($member->id, 8, '0', STR_PAD_LEFT),
                    'member_id' => $member->id,
                    'accountable_type' => ShareAccount::class,
                    'accountable_id' => $shareAccount->id,
                    'status' => 'active',
                    'opening_date' => now()->subMonths(rand(12, 60)),
                ]);
                
                // Create 1-4 individual share certificates (purchases over time)
                $certificateCount = rand(1, 4);
                $totalUnits = 0;
                
                for ($k = 0; $k < $certificateCount; $k++) {
                    $sharesInCert = rand(5, 50);
                    $shareValueAtPurchase = rand(900, 1100); // Historical price variance
                    
                    $share = Share::factory()->create([
                        'member_id' => $member->id,
                        'share_account_id' => $shareAccount->id,
                        'shares_count' => $sharesInCert,
                        'share_value' => $shareValueAtPurchase,
                        'total_value' => $sharesInCert * $shareValueAtPurchase,
                        'status' => 'active',
                        'purchase_date' => now()->subMonths(rand(1, 48)),
                    ]);
                    
                    $totalShareCertificates++;
                    $totalUnits += $sharesInCert;
                }
                
                // Calculate dividends (assume 10% annual on share value)
                $dividendsEarned = $totalUnits * 100; // 100 per share earned
                $dividendsPaid = $dividendsEarned * 0.7; // 70% paid
                
                // Update share account with calculated totals
                $shareAccount->update([
                    'share_units' => $totalUnits,
                    'total_share_value' => $totalUnits * 1000, // At current price
                    'dividends_earned' => $dividendsEarned,
                    'dividends_paid' => $dividendsPaid,
                    'dividends_pending' => $dividendsEarned - $dividendsPaid,
                    'last_activity_date' => now()->subDays(rand(1, 60)),
                ]);
                
                $this->command->info("  → Created share account with {$certificateCount} certificates ({$totalUnits} units)");
            }
        }
        
        $this->command->info('');
        $this->command->info('✅ Polymorphic accounts seeded successfully!');
        $this->command->info('');
        $this->command->info('📊 Accounts breakdown:');
        $this->command->info('  - Savings Accounts: ' . Account::where('accountable_type', SavingsAccount::class)->count());
        $this->command->info('  - Loan Accounts: ' . Account::where('accountable_type', LoanAccount::class)->count());
        $this->command->info('  - Share Accounts: ' . Account::where('accountable_type', ShareAccount::class)->count());
        $this->command->info('');
        $this->command->info('📝 Entity records:');
        $this->command->info("  - Individual Loans: {$totalLoans}");
        $this->command->info("  - Share Certificates: {$totalShareCertificates}");
        $this->command->info('');
    }
    
    /**
     * Get faker instance
     */
    private function faker()
    {
        return \Faker\Factory::create();
    }
}
