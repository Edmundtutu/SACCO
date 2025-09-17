<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Transaction Limits
    |--------------------------------------------------------------------------
    */
    'minimum_deposit_amount' => env('SACCO_MIN_DEPOSIT', 1000),
    'minimum_withdrawal_amount' => env('SACCO_MIN_WITHDRAWAL', 1000),
    'minimum_repayment_amount' => env('SACCO_MIN_REPAYMENT', 1000),
    'maximum_transaction_amount' => env('SACCO_MAX_TRANSACTION', 10000000),

    /*
    |--------------------------------------------------------------------------
    | Daily Limits
    |--------------------------------------------------------------------------
    */
    'daily_deposit_limit' => env('SACCO_DAILY_DEPOSIT_LIMIT', 1000000),
    'daily_withdrawal_limit' => env('SACCO_DAILY_WITHDRAWAL_LIMIT', 500000),
    'daily_share_purchase_limit' => env('SACCO_DAILY_SHARE_LIMIT', 1000000),
    'max_daily_transactions' => env('SACCO_MAX_DAILY_TRANSACTIONS', 10),

    /*
    |--------------------------------------------------------------------------
    | Share Configuration
    |--------------------------------------------------------------------------
    */
    'share_value' => env('SACCO_SHARE_VALUE', 10000),
    'max_shares_per_purchase' => env('SACCO_MAX_SHARES_PER_PURCHASE', 100),
    'minimum_share_capital' => env('SACCO_MIN_SHARE_CAPITAL', 50000),

    /*
    |--------------------------------------------------------------------------
    | Interest Rates
    |--------------------------------------------------------------------------
    */
    'default_savings_interest_rate' => env('SACCO_SAVINGS_INTEREST_RATE', 5.0),
    'default_loan_interest_rate' => env('SACCO_LOAN_INTEREST_RATE', 12.0),

    /*
    |--------------------------------------------------------------------------
    | Fees
    |--------------------------------------------------------------------------
    */
    'withdrawal_fee' => env('SACCO_WITHDRAWAL_FEE', 1000),
    'loan_processing_fee_rate' => env('SACCO_LOAN_PROCESSING_FEE_RATE', 2.0),

    /*
    |--------------------------------------------------------------------------
    | Business Rules
    |--------------------------------------------------------------------------
    */
    'require_guarantors_for_loans' => env('SACCO_REQUIRE_GUARANTORS', true),
    'minimum_guarantors' => env('SACCO_MIN_GUARANTORS', 2),
    'loan_to_savings_ratio' => env('SACCO_LOAN_TO_SAVINGS_RATIO', 3.0),

    /*
    |--------------------------------------------------------------------------
    | Approval Thresholds
    |--------------------------------------------------------------------------
    */
    'withdrawal_approval_threshold' => env('SACCO_WITHDRAWAL_APPROVAL_THRESHOLD', 100000),
    'loan_approval_threshold' => env('SACCO_LOAN_APPROVAL_THRESHOLD', 500000),
];
