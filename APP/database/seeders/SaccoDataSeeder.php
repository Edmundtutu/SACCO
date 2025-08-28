<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Member;
use App\Models\SavingsProduct;
use App\Models\LoanProduct;
use App\Models\Account;
use App\Models\ChartOfAccount;
use Illuminate\Support\Facades\Hash;

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
            'member_number' => 'ADMIN001',
            'role' => 'admin',
            'status' => 'active',
            'phone' => '+1234567890',
            'national_id' => 'ADMIN123456',
            'date_of_birth' => '1980-01-01',
            'gender' => 'male',
            'address' => 'SACCO Head Office',
            'occupation' => 'Administrator',
            'monthly_income' => 50000,
            'membership_date' => now(),
            'approved_at' => now(),
        ]);

        // Create Loan Officer
        $loanOfficer = User::create([
            'name' => 'John Loan Officer',
            'email' => 'loans@sacco.com',
            'password' => Hash::make('password123'),
            'member_number' => 'LO001',
            'role' => 'loan_officer',
            'status' => 'active',
            'phone' => '+1234567891',
            'national_id' => 'LO123456',
            'date_of_birth' => '1985-01-01',
            'gender' => 'male',
            'address' => 'SACCO Office',
            'occupation' => 'Loan Officer',
            'monthly_income' => 30000,
            'membership_date' => now(),
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);

        // Create Savings Products
        $this->createSavingsProducts();
        
        // Create Loan Products
        $this->createLoanProducts();
        
        // Create Sample Members
        $this->createSampleMembers($admin);
        
        echo "SACCO data seeded successfully!\n";
        echo "Admin Login: admin@sacco.com / password123\n";
        echo "Loan Officer Login: loans@sacco.com / password123\n";
    }

    private function createChartOfAccounts()
    {
        $accounts = [
            // Assets
            ['1001', 'Cash in Hand', 'asset', 'current_asset', 'debit'],
            ['1002', 'Bank Account', 'asset', 'current_asset', 'debit'],
            ['1003', 'Loans to Members', 'asset', 'current_asset', 'debit'],
            ['1004', 'Interest Receivable', 'asset', 'current_asset', 'debit'],
            ['1005', 'Office Equipment', 'asset', 'fixed_asset', 'debit'],
            
            // Liabilities
            ['2001', 'Member Savings', 'liability', 'current_liability', 'credit'],
            ['2002', 'Member Shares', 'liability', 'long_term_liability', 'credit'],
            ['2003', 'Accrued Expenses', 'liability', 'current_liability', 'credit'],
            
            // Equity
            ['3001', 'Retained Earnings', 'equity', 'retained_earnings', 'credit'],
            ['3002', 'Current Year Earnings', 'equity', 'retained_earnings', 'credit'],
            
            // Income
            ['4001', 'Interest on Loans', 'income', 'operating_income', 'credit'],
            ['4002', 'Fee Income', 'income', 'operating_income', 'credit'],
            ['4003', 'Other Income', 'income', 'non_operating_income', 'credit'],
            
            // Expenses
            ['5001', 'Interest Expense', 'expense', 'operating_expense', 'debit'],
            ['5002', 'Administrative Expenses', 'expense', 'operating_expense', 'debit'],
            ['5003', 'Office Rent', 'expense', 'operating_expense', 'debit'],
            ['5004', 'Utilities', 'expense', 'operating_expense', 'debit'],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::create([
                'account_code' => $account[0],
                'account_name' => $account[1],
                'account_type' => $account[2],
                'account_subtype' => $account[3],
                'normal_balance' => $account[4],
                'is_active' => true,
                'allow_manual_entry' => true,
                'level' => 1,
                'opening_balance' => 0,
                'opening_date' => now(),
            ]);
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

    private function createSampleMembers($admin)
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
            $memberNumber = 'M' . str_pad($index + 1, 6, '0', STR_PAD_LEFT);
            
            $member = User::create([
                'name' => $memberData['name'],
                'email' => $memberData['email'],
                'password' => Hash::make('password123'),
                'member_number' => $memberNumber,
                'role' => 'member',
                'status' => 'active',
                'phone' => $memberData['phone'],
                'national_id' => $memberData['national_id'],
                'date_of_birth' => '1990-01-01',
                'gender' => 'female',
                'address' => 'Sample Address ' . ($index + 1),
                'occupation' => $memberData['occupation'],
                'monthly_income' => $memberData['monthly_income'],
                'membership_date' => now()->subDays(rand(30, 365)),
                'approved_at' => now()->subDays(rand(1, 30)),
                'approved_by' => $admin->id,
            ]);

            // Create member profile
            $member->memberProfile()->create([
                'next_of_kin_name' => 'Next of Kin ' . ($index + 1),
                'next_of_kin_relationship' => 'Spouse',
                'next_of_kin_phone' => '+1234567' . (900 + $index),
                'next_of_kin_address' => 'Next of Kin Address ' . ($index + 1),
                'employer_name' => 'Employer ' . ($index + 1),
                'employer_address' => 'Employer Address ' . ($index + 1),
                'employer_phone' => '+1234567' . (950 + $index),
            ]);

            // Create compulsory savings account for each member
            $compulsorySavings = SavingsProduct::where('type', 'compulsory')->first();
            if ($compulsorySavings) {
                $accountNumber = 'SA' . str_pad($index + 1, 8, '0', STR_PAD_LEFT);
                
                Account::create([
                    'account_number' => $accountNumber,
                    'member_id' => $member->id,
                    'savings_product_id' => $compulsorySavings->id,
                    'balance' => rand(5000, 50000),
                    'available_balance' => rand(5000, 50000),
                    'minimum_balance' => $compulsorySavings->minimum_balance,
                    'status' => 'active',
                    'last_transaction_date' => now(),
                ]);
            }
        }
    }
}