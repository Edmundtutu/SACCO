<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Phase 2 — Financial Feature Expansion Feature Flags
    |--------------------------------------------------------------------------
    |
    | These flags gate the expense and income transaction pipelines introduced
    | in Phase 2.  Set the corresponding environment variable to "true" to
    | enable each feature.  Both default to false so existing deployments are
    | unaffected until an operator explicitly enables them.
    |
    */
    'enable_expense_transactions' => env('FINANCIAL_ENABLE_EXPENSE', false),
    'enable_income_transactions'  => env('FINANCIAL_ENABLE_INCOME', false),

    /*
    |--------------------------------------------------------------------------
    | Expense Categories
    |--------------------------------------------------------------------------
    |
    | Maps a human-readable category label to its GL account code.
    | Add new categories here; the seeder will ensure the corresponding
    | chart-of-accounts row exists before any transaction is processed.
    |
    */
    'expense_categories' => [
        'stationery'   => ['code' => '5004', 'name' => 'Stationery & Office Supplies'],
        'transport'    => ['code' => '5005', 'name' => 'Transport & Travel'],
        'utilities'    => ['code' => '5006', 'name' => 'Utilities (Electricity/Water)'],
        'salaries'     => ['code' => '5007', 'name' => 'Staff Salaries & Wages'],
        'rent'         => ['code' => '5008', 'name' => 'Rent & Premises'],
        'maintenance'  => ['code' => '5009', 'name' => 'Maintenance & Repairs'],
        'other'        => ['code' => '5001', 'name' => 'Operating Expenses'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Income Categories (Non-loan)
    |--------------------------------------------------------------------------
    |
    | Maps a human-readable category label to its GL account code.
    |
    */
    'income_categories' => [
        'membership_fee' => ['code' => '4004', 'name' => 'Membership Fees'],
        'service_fee'    => ['code' => '4005', 'name' => 'Service Fees'],
        'registration'   => ['code' => '4006', 'name' => 'Registration Fees'],
        'investment'     => ['code' => '4007', 'name' => 'Investment Income'],
        'other'          => ['code' => '4002', 'name' => 'Fee Income'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Minimum Amounts
    |--------------------------------------------------------------------------
    */
    'minimum_expense_amount' => env('FINANCIAL_MIN_EXPENSE', 1),
    'minimum_income_amount'  => env('FINANCIAL_MIN_INCOME', 1),
];
