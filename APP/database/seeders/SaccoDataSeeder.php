<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SavingsProduct;
use App\Models\LoanProduct;
use App\Models\Account;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Hash;
use App\Models\Membership\IndividualProfile;
use App\Models\Membership\Membership;

class SaccoDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Chart of Accounts
        $this->createChartOfAccounts();

        // Create Admin User
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@sacco.com',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'status' => 'active',
            'account_verified_at' => now(),
            'membership_date' => now(),
        ]);

        // Create Loan Officer
        $loanOfficer = User::create([
            'name' => 'John Loan Officer',
            'email' => 'loans@sacco.com',
            'password' => Hash::make('password123'),
            'role' => 'staff_level_2',
            'status' => 'active',
            'account_verified_at' => now(),
            'membership_date' => now(),
        ]);

        // Create Savings Products
        $this->createSavingsProducts();

        // Create Loan Products
        $this->createLoanProducts();

        // Create Sample Members
        $this->createSampleMembers($admin, $loanOfficer);

        echo "SACCO data seeded successfully!\n";
        echo "Admin Login: admin@sacco.com / password123\n";
        echo "Loan Officer Login: loans@sacco.com / password123\n";
    }

    private function createChartOfAccounts()
    {
        // Database seeder or migration for Chart of Accounts
        $chartOfAccounts = [
            // ASSETS
            ['account_code' => '1000', 'account_name' => 'ASSETS', 'account_type' => 'asset', 'normal_balance' => 'debit', 'level' => 1],
            ['account_code' => '1001', 'account_name' => 'Cash in Hand', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1000', 'level' => 2],
            ['account_code' => '1002', 'account_name' => 'Cash at Bank', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1000', 'level' => 2],
            ['account_code' => '1100', 'account_name' => 'Loans Receivable', 'account_type' => 'asset', 'normal_balance' => 'debit', 'parent_code' => '1000', 'level' => 2],

            // LIABILITIES
            ['account_code' => '2000', 'account_name' => 'LIABILITIES', 'account_type' => 'liability', 'normal_balance' => 'credit', 'level' => 1],
            ['account_code' => '2001', 'account_name' => 'Member Savings Payable', 'account_type' => 'liability', 'normal_balance' => 'credit', 'parent_code' => '2000', 'level' => 2],
            ['account_code' => '2002', 'account_name' => 'Dividends Payable', 'account_type' => 'liability', 'normal_balance' => 'credit', 'parent_code' => '2000', 'level' => 2],

            // EQUITY
            ['account_code' => '3000', 'account_name' => 'EQUITY', 'account_type' => 'equity', 'normal_balance' => 'credit', 'level' => 1],
            ['account_code' => '3001', 'account_name' => 'Member Share Capital', 'account_type' => 'equity', 'normal_balance' => 'credit', 'parent_code' => '3000', 'level' => 2],
            ['account_code' => '3002', 'account_name' => 'Retained Earnings', 'account_type' => 'equity', 'normal_balance' => 'credit', 'parent_code' => '3000', 'level' => 2],

            // INCOME
            ['account_code' => '4000', 'account_name' => 'INCOME', 'account_type' => 'income', 'normal_balance' => 'credit', 'level' => 1],
            ['account_code' => '4001', 'account_name' => 'Loan Interest Income', 'account_type' => 'income', 'normal_balance' => 'credit', 'parent_code' => '4000', 'level' => 2],
            ['account_code' => '4002', 'account_name' => 'Fee Income', 'account_type' => 'income', 'normal_balance' => 'credit', 'parent_code' => '4000', 'level' => 2],

            // EXPENSES
            ['account_code' => '5000', 'account_name' => 'EXPENSES', 'account_type' => 'expense', 'normal_balance' => 'debit', 'level' => 1],
            ['account_code' => '5001', 'account_name' => 'Operating Expenses', 'account_type' => 'expense', 'normal_balance' => 'debit', 'parent_code' => '5000', 'level' => 2],
        ];

        foreach ($chartOfAccounts as $account) {
            DB::table('chart_of_accounts')->insert(array_merge($account, [
                'is_active' => true,
                'allow_manual_entry' => true,
                'opening_balance' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]));
        }
    }

    private function createSavingsProducts()
    {
        $products = [
            [
                'name' => 'Compulsory Savings',
                'code' => 'CS001',
                'description' => 'Mandatory savings for all members',
                'type' => 'compulsory',
                'minimum_balance' => 1000,
                'maximum_balance' => null,
                'interest_rate' => 5.0,
                'interest_calculation' => 'simple',
                'interest_payment_frequency' => 'annually',
                'minimum_monthly_contribution' => 500,
                'maturity_period_months' => null,
                'withdrawal_fee' => 100,
                'allow_partial_withdrawals' => false,
                'minimum_notice_days' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Voluntary Savings',
                'code' => 'VS001',
                'description' => 'Flexible savings account with easy access',
                'type' => 'voluntary',
                'minimum_balance' => 500,
                'maximum_balance' => null,
                'interest_rate' => 3.0,
                'interest_calculation' => 'simple',
                'interest_payment_frequency' => 'annually',
                'minimum_monthly_contribution' => null,
                'maturity_period_months' => null,
                'withdrawal_fee' => 50,
                'allow_partial_withdrawals' => true,
                'minimum_notice_days' => 0,
                'is_active' => true,
            ],
            [
                'name' => 'Fixed Deposit',
                'code' => 'FD001',
                'description' => '12-month fixed deposit with higher interest',
                'type' => 'fixed_deposit',
                'minimum_balance' => 10000,
                'maximum_balance' => null,
                'interest_rate' => 8.0,
                'interest_calculation' => 'compound',
                'interest_payment_frequency' => 'annually',
                'minimum_monthly_contribution' => null,
                'maturity_period_months' => 12,
                'withdrawal_fee' => 500,
                'allow_partial_withdrawals' => false,
                'minimum_notice_days' => 90,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            SavingsProduct::create($product);
        }
    }

    private function createLoanProducts()
    {
        $products = [
            [
                'name' => 'Personal Loan',
                'code' => 'PL001',
                'description' => 'General purpose personal loan',
                'type' => 'personal',
                'minimum_amount' => 5000,
                'maximum_amount' => 100000,
                'interest_rate' => 12.0,
                'interest_calculation' => 'reducing_balance',
                'minimum_period_months' => 6,
                'maximum_period_months' => 36,
                'processing_fee_rate' => 2.0,
                'insurance_fee_rate' => 1.0,
                'required_guarantors' => 2,
                'guarantor_savings_multiplier' => 3.0,
                'grace_period_days' => 5,
                'penalty_rate' => 2.0,
                'minimum_savings_months' => 6.0,
                'savings_to_loan_ratio' => 3.0,
                'require_collateral' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Emergency Loan',
                'code' => 'EL001',
                'description' => 'Quick access emergency loan',
                'type' => 'emergency',
                'minimum_amount' => 1000,
                'maximum_amount' => 50000,
                'interest_rate' => 15.0,
                'interest_calculation' => 'reducing_balance',
                'minimum_period_months' => 3,
                'maximum_period_months' => 12,
                'processing_fee_rate' => 1.0,
                'insurance_fee_rate' => 0.5,
                'required_guarantors' => 1,
                'guarantor_savings_multiplier' => 2.0,
                'grace_period_days' => 3,
                'penalty_rate' => 3.0,
                'minimum_savings_months' => 3.0,
                'savings_to_loan_ratio' => 2.0,
                'require_collateral' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Development Loan',
                'code' => 'DL001',
                'description' => 'Long-term development and investment loan',
                'type' => 'development',
                'minimum_amount' => 50000,
                'maximum_amount' => 500000,
                'interest_rate' => 10.0,
                'interest_calculation' => 'reducing_balance',
                'minimum_period_months' => 12,
                'maximum_period_months' => 60,
                'processing_fee_rate' => 2.5,
                'insurance_fee_rate' => 1.5,
                'required_guarantors' => 3,
                'guarantor_savings_multiplier' => 4.0,
                'grace_period_days' => 7,
                'penalty_rate' => 1.5,
                'minimum_savings_months' => 12.0,
                'savings_to_loan_ratio' => 4.0,
                'require_collateral' => true,
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            LoanProduct::create($product);
        }
    }

    private function createSampleMembers($admin, $loanOfficer)
    {
        // Create sample members
        $members = [
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone' => '+1234567892',
                'national_id' => 'MEM001',
                'occupation' => 'Teacher',
                'monthly_income' => 25000,
            ],
            [
                'name' => 'Robert Johnson',
                'email' => 'robert@example.com',
                'phone' => '+1234567893',
                'national_id' => 'MEM002',
                'occupation' => 'Farmer',
                'monthly_income' => 15000,
            ],
            [
                'name' => 'Mary Williams',
                'email' => 'mary@example.com',
                'phone' => '+1234567894',
                'national_id' => 'MEM003',
                'occupation' => 'Nurse',
                'monthly_income' => 30000,
            ],
        ];

        foreach ($members as $index => $memberData) {
            // Create user (member)
            $member = User::create([
                'name' => $memberData['name'],
                'email' => $memberData['email'],
                'password' => Hash::make('password123'),
                'role' => 'member',
                'status' => 'active',
                'account_verified_at' => now(),
                'membership_date' => now()->subDays(rand(30, 365)),
            ]);

            // Create Individual Profile (KYC + extras)
            $profile = IndividualProfile::create([
                'phone' => $memberData['phone'],
                'national_id' => $memberData['national_id'],
                'date_of_birth' => '1990-01-01',
                'gender' => 'female',
                'occupation' => $memberData['occupation'],
                'monthly_income' => $memberData['monthly_income'],
                'referee' => $admin->id,
                'next_of_kin_name' => 'Next of Kin ' . ($index + 1),
                'next_of_kin_relationship' => 'Spouse',
                'next_of_kin_phone' => '+1234567' . (900 + $index),
                'next_of_kin_address' => 'Next of Kin Address ' . ($index + 1),
                'emergency_contact_name' => 'Emergency ' . ($index + 1),
                'emergency_contact_phone' => '+1234567' . (980 + $index),
                'employer_name' => 'Employer ' . ($index + 1),
                'bank_name' => 'Sacco Bank',
                'bank_account_number' => 'BA' . str_pad((string)($index + 1), 10, '0', STR_PAD_LEFT),
                'profile_photo_path' => null,
                'id_copy_path' => null,
                'signature_path' => null,
                'additional_notes' => 'Sample profile seeded',
            ]);

            // Create Membership record linking user and profile
            Membership::create([
                'user_id' => $member->id,
                'profile_type' => IndividualProfile::class,
                'profile_id' => $profile->id,
                'approval_status' => 'approved',
                'approved_by_level_1' => $admin->id,
                'approved_at_level_1' => now()->subDays(rand(1, 30)),
                'approved_by_level_2' => $loanOfficer->id,
                'approved_at_level_2' => now()->subDays(rand(1, 15)),
                'approved_by_level_3' => null,
                'approved_at_level_3' => null,
            ]);

            // Create compulsory savings account for each member
            $compulsorySavings = SavingsProduct::where('type', 'compulsory')->first();
            if ($compulsorySavings) {
                $accountNumber = 'SA' . str_pad($index + 1, 8, '0', STR_PAD_LEFT);
                $balance = rand(5000, 50000);
                $available = max(0, $balance - rand(0, 1000));

                Account::create([
                    'account_number' => $accountNumber,
                    'member_id' => $member->id,
                    'savings_product_id' => $compulsorySavings->id,
                    'account_type' => 'savings',
                    'balance' => $balance,
                    'available_balance' => $available,
                    'minimum_balance' => $compulsorySavings->minimum_balance,
                    'interest_earned' => 0,
                    'status' => 'active',
                    'last_transaction_date' => now(),
                ]);
            }
        }
    }
}
