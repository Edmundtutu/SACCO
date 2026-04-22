<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Phase 2 — Expense & Income Chart of Accounts Seeder
 *
 * Idempotent: uses insertOrIgnore / upsert so it is safe to re-run at any
 * time without duplicating rows.  Run after ChartOfAccountsSeeder.
 *
 * Usage:
 *   php artisan db:seed --class=ExpenseIncomeAccountSeeder
 */
class ExpenseIncomeAccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // ── Additional Income accounts (4xxx) ──────────────────────────
            [
                'account_code'       => '4004',
                'account_name'       => 'Membership Fees',
                'account_type'       => 'income',
                'account_subtype'    => 'fee_income',
                'parent_code'        => '4000',
                'normal_balance'     => 'credit',
                'level'              => 2,
                'is_active'          => true,
                'allow_manual_entry' => true,
                'description'        => 'Income from member registration and annual membership fees',
                'opening_balance'    => 0,
                'opening_date'       => now()->startOfYear(),
            ],
            [
                'account_code'       => '4005',
                'account_name'       => 'Service Fees',
                'account_type'       => 'income',
                'account_subtype'    => 'fee_income',
                'parent_code'        => '4000',
                'normal_balance'     => 'credit',
                'level'              => 2,
                'is_active'          => true,
                'allow_manual_entry' => true,
                'description'        => 'Income from various SACCO service charges',
                'opening_balance'    => 0,
                'opening_date'       => now()->startOfYear(),
            ],
            [
                'account_code'       => '4006',
                'account_name'       => 'Registration Fees',
                'account_type'       => 'income',
                'account_subtype'    => 'fee_income',
                'parent_code'        => '4000',
                'normal_balance'     => 'credit',
                'level'              => 2,
                'is_active'          => true,
                'allow_manual_entry' => true,
                'description'        => 'One-time registration fees collected from new members',
                'opening_balance'    => 0,
                'opening_date'       => now()->startOfYear(),
            ],
            [
                'account_code'       => '4007',
                'account_name'       => 'Investment Income',
                'account_type'       => 'income',
                'account_subtype'    => 'interest_income',
                'parent_code'        => '4000',
                'normal_balance'     => 'credit',
                'level'              => 2,
                'is_active'          => true,
                'allow_manual_entry' => true,
                'description'        => 'Income from external investments made by the SACCO',
                'opening_balance'    => 0,
                'opening_date'       => now()->startOfYear(),
            ],

            // ── Additional Expense accounts (5xxx) ────────────────────────
            [
                'account_code'       => '5004',
                'account_name'       => 'Stationery & Office Supplies',
                'account_type'       => 'expense',
                'account_subtype'    => 'operating_expense',
                'parent_code'        => '5000',
                'normal_balance'     => 'debit',
                'level'              => 2,
                'is_active'          => true,
                'allow_manual_entry' => true,
                'description'        => 'Cost of stationery, printing, and office consumables',
                'opening_balance'    => 0,
                'opening_date'       => now()->startOfYear(),
            ],
            [
                'account_code'       => '5005',
                'account_name'       => 'Transport & Travel',
                'account_type'       => 'expense',
                'account_subtype'    => 'operating_expense',
                'parent_code'        => '5000',
                'normal_balance'     => 'debit',
                'level'              => 2,
                'is_active'          => true,
                'allow_manual_entry' => true,
                'description'        => 'Transport, fuel, and travel costs incurred by staff',
                'opening_balance'    => 0,
                'opening_date'       => now()->startOfYear(),
            ],
            [
                'account_code'       => '5006',
                'account_name'       => 'Utilities (Electricity/Water)',
                'account_type'       => 'expense',
                'account_subtype'    => 'operating_expense',
                'parent_code'        => '5000',
                'normal_balance'     => 'debit',
                'level'              => 2,
                'is_active'          => true,
                'allow_manual_entry' => true,
                'description'        => 'Electricity, water, and internet utility bills',
                'opening_balance'    => 0,
                'opening_date'       => now()->startOfYear(),
            ],
            [
                'account_code'       => '5007',
                'account_name'       => 'Staff Salaries & Wages',
                'account_type'       => 'expense',
                'account_subtype'    => 'operating_expense',
                'parent_code'        => '5000',
                'normal_balance'     => 'debit',
                'level'              => 2,
                'is_active'          => true,
                'allow_manual_entry' => true,
                'description'        => 'Monthly salaries and wages paid to SACCO staff',
                'opening_balance'    => 0,
                'opening_date'       => now()->startOfYear(),
            ],
            [
                'account_code'       => '5008',
                'account_name'       => 'Rent & Premises',
                'account_type'       => 'expense',
                'account_subtype'    => 'operating_expense',
                'parent_code'        => '5000',
                'normal_balance'     => 'debit',
                'level'              => 2,
                'is_active'          => true,
                'allow_manual_entry' => true,
                'description'        => 'Office rent and premises-related costs',
                'opening_balance'    => 0,
                'opening_date'       => now()->startOfYear(),
            ],
            [
                'account_code'       => '5009',
                'account_name'       => 'Maintenance & Repairs',
                'account_type'       => 'expense',
                'account_subtype'    => 'operating_expense',
                'parent_code'        => '5000',
                'normal_balance'     => 'debit',
                'level'              => 2,
                'is_active'          => true,
                'allow_manual_entry' => true,
                'description'        => 'Equipment maintenance, repairs, and servicing costs',
                'opening_balance'    => 0,
                'opening_date'       => now()->startOfYear(),
            ],
        ];

        foreach ($accounts as $account) {
            DB::table('chart_of_accounts')->updateOrInsert(
                ['account_code' => $account['account_code']],
                array_merge($account, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('Phase 2 expense/income chart of accounts seeded (idempotent).');
    }
}
