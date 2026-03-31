<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Phase 0: Financial Core Stabilization – Feature Flags
    |--------------------------------------------------------------------------
    |
    | These flags control staged rollout and provide rollback safety for
    | Phase 0 changes. Toggle via environment variables.
    |
    */

    /*
     * Route Api\LoansController::repay() through TransactionService.
     * When false the endpoint falls back to a direct DB write path.
     * Default: true (new unified path active).
     */
    'use_transaction_service_for_legacy_repay' => env(
        'FEATURE_USE_TRANSACTION_SERVICE_FOR_LEGACY_REPAY',
        true
    ),

    /*
     * Use the centralized PaymentMethodAccountResolver in handlers so that
     * the correct GL asset account (cash/bank/mobile-money) is selected based
     * on payment_method instead of always defaulting to 1001 (Cash in Hand).
     * Default: true (centralized mapping active).
     */
    'use_centralized_payment_method_mapping' => env(
        'FEATURE_USE_CENTRALIZED_PAYMENT_METHOD_MAPPING',
        true
    ),

    /*
     * GL double-entry balance enforcement.
     * Stage 1 (false/default): log a warning when debits ≠ credits but do
     *   not block the transaction (monitor mode).
     * Stage 2 (true): throw an exception and roll back the transaction.
     */
    'enforce_gl_balance_check' => env(
        'FEATURE_ENFORCE_GL_BALANCE_CHECK',
        false
    ),
];
