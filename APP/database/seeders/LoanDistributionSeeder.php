<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Account;
use App\Models\Loan;
use App\Models\LoanAccount;
use App\Models\LoanProduct;
use App\Models\LoanGuarantor;
use App\Models\LoanRepayment;
use App\Models\Share;
use App\Models\ShareAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * LoanDistributionSeeder - Creates loans and shares for existing accounts
 * Creates: Loan records, Loan repayments, Guarantors, Share certificates
 * Updates aggregate values in LoanAccount and ShareAccount
 * Idempotent: checks for existing loans/shares before creating
 */
class LoanDistributionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🔄 Starting LoanDistributionSeeder...');

        $loanProducts = LoanProduct::all();
        if ($loanProducts->isEmpty()) {
            $this->command->warn('⚠️  No loan products found. Skipping loan creation.');
        }

        $loansCreated = 0;
        $repaymentsCreated = 0;
        $guarantorsCreated = 0;
        $sharesCreated = 0;

        // Process loan accounts
        if ($loanProducts->isNotEmpty()) {
            $loanAccounts = Account::where('accountable_type', LoanAccount::class)
                ->with(['accountable', 'member'])
                ->get();

            $this->command->info('📋 Found ' . $loanAccounts->count() . ' loan accounts to process');

            foreach ($loanAccounts as $index => $account) {
                DB::transaction(function () use ($account, $loanProducts, &$loansCreated, &$repaymentsCreated, &$guarantorsCreated) {
                    $loanAccount = $account->accountable;

                    // Skip if already has loans
                    if (Loan::where('loan_account_id', $loanAccount->id)->exists()) {
                        return;
                    }

                    // Create 1-3 loans per account
                    $numLoans = rand(1, 3);
                    $totalDisbursed = 0;
                    $totalRepaid = 0;
                    $totalOutstanding = 0;

                    for ($i = 0; $i < $numLoans; $i++) {
                        $loanProduct = $loanProducts->random();

                        $loan = Loan::factory()->create([
                            'member_id' => $account->member_id,
                            'loan_account_id' => $loanAccount->id,
                            'loan_product_id' => $loanProduct->id,
                        ]);

                        $totalDisbursed += $loan->approved_amount ?? $loan->requested_amount;
                        $totalRepaid += $loan->total_repaid ?? 0;
                        $totalOutstanding += $loan->balance_outstanding ?? 0;

                        $loansCreated++;

                        // Create 1-2 guarantors per loan
                        $numGuarantors = rand(1, 2);
                        for ($g = 0; $g < $numGuarantors; $g++) {
                            LoanGuarantor::factory()->create([
                                'loan_id' => $loan->id,
                            ]);
                            $guarantorsCreated++;
                        }

                        // Create 3-10 repayments per loan
                        $numRepayments = rand(3, 10);
                        for ($r = 0; $r < $numRepayments; $r++) {
                            LoanRepayment::factory()->create([
                                'loan_id' => $loan->id,
                            ]);
                            $repaymentsCreated++;
                        }
                    }

                    // Update loan account aggregates
                    $loanAccount->update([
                        'total_disbursed_amount' => $totalDisbursed,
                        'total_repaid_amount' => $totalRepaid,
                        'current_outstanding' => $totalOutstanding,
                        'last_activity_date' => now()->subDays(rand(1, 30)),
                    ]);
                });

                if ((($index + 1) % 10) == 0) {
                    $this->command->info('  Processed ' . ($index + 1) . '/' . $loanAccounts->count() . ' loan accounts...');
                }
            }
        }

        // Process share accounts
        $shareAccounts = Account::where('accountable_type', ShareAccount::class)
            ->with(['accountable', 'member'])
            ->get();

        $this->command->info('📋 Found ' . $shareAccounts->count() . ' share accounts to process');

        foreach ($shareAccounts as $index => $account) {
            DB::transaction(function () use ($account, &$sharesCreated) {
                $shareAccount = $account->accountable;

                // Skip if already has shares
                if (Share::where('share_account_id', $shareAccount->id)->exists()) {
                    return;
                }

                // Create 1-5 share certificates per account
                $numShares = rand(1, 5);
                $totalUnits = 0;
                $sharePrice = 1000; // Standard price per share unit

                for ($i = 0; $i < $numShares; $i++) {
                    $share = Share::factory()->create([
                        'member_id' => $account->member_id,
                        'share_account_id' => $shareAccount->id,
                    ]);

                    $totalUnits += $share->number_of_shares ?? 1;
                    $sharesCreated++;
                }

                // Update share account aggregates
                $shareAccount->update([
                    'share_units' => $totalUnits,
                    'total_share_value' => $totalUnits * $sharePrice,
                    'last_activity_date' => now()->subDays(rand(1, 30)),
                ]);
            });

            if ((($index + 1) % 10) == 0) {
                $this->command->info('  Processed ' . ($index + 1) . '/' . $shareAccounts->count() . ' share accounts...');
            }
        }

        $this->command->info('✅ LoanDistributionSeeder complete!');
        $this->command->info('   - Loans: ' . $loansCreated);
        $this->command->info('   - Repayments: ' . $repaymentsCreated);
        $this->command->info('   - Guarantors: ' . $guarantorsCreated);
        $this->command->info('   - Share Certificates: ' . $sharesCreated);
    }
}
