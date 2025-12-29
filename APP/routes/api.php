<?php

use App\Http\Controllers\Api\AccountsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LoansController;
use App\Http\Controllers\Api\ReportsController;
use App\Http\Controllers\Api\SavingsController;
use App\Http\Controllers\Api\SavingsGoalController;
use App\Http\Controllers\Api\SharesController;
use App\Http\Controllers\Api\Transactions\LoanTransactionController;
use App\Http\Controllers\Api\Transactions\SavingsTransactionController;
use App\Http\Controllers\Api\Transactions\ShareTransactionController;
use App\Http\Controllers\Api\Transactions\TransactionController;
use App\Http\Controllers\Api\Transactions\WalletTransactionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SACCO API Routes
|--------------------------------------------------------------------------
|
| Comprehensive REST API for SACCO management system
|
*/

// Authentication routes
Route::group(['prefix' => 'auth'], function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::post('change-password', [AuthController::class, 'changePassword']);

        // Admin only routes
        Route::post('approve-member/{memberId}', [AuthController::class, 'approveMember']);
    });
});

// Protected routes (require authentication)
Route::group(['middleware' => 'auth:api'], function () {

    // Polymorphic Accounts (supports ?type=savings|loan|share)
    Route::group(['prefix' => 'accounts'], function () {
        Route::get('/', [AccountsController::class, 'index']);
        Route::get('summary', [AccountsController::class, 'summary']);
        Route::get('{accountId}', [AccountsController::class, 'show']);
        Route::get('{accountId}/transactions', [AccountsController::class, 'transactions']);
    });

    // Savings & Accounts
    Route::group(['prefix' => 'savings'], function () {
        Route::get('accounts', [SavingsController::class, 'getAccounts']);
        Route::get('products', [SavingsController::class, 'getSavingsProducts']);
        Route::get('accounts/{accountId}/transactions', [SavingsController::class, 'getTransactions']);
        Route::apiResource('goals', SavingsGoalController::class)->except(['create', 'edit']);
    });

    // Loans
    Route::group(['prefix' => 'loans'], function () {
        Route::get('/', [LoansController::class, 'index']);
        Route::post('apply', [LoansController::class, 'apply']);

        // Put static routes BEFORE dynamic {loanId}
        Route::get('products', [LoansController::class, 'getLoanProducts']);

        Route::get('{loanId}', [LoansController::class, 'show']);
        // This method action has been revised in a dedicated LoansTransactionController: Should be replaced with the corresponding route below
        Route::post('{loanId}/repay', [LoansController::class, 'repay']);
        // This method action has been revised in a dedicated LoansTransactionController: Should be replaced with the corresponding route below
        Route::get('{loanId}/schedule', [LoansController::class, 'getRepaymentSchedule']);

        // Admin/Staff only
        Route::post('{loanId}/approve', [LoansController::class, 'approve']);
        // This method action has been revised in a dedicated LoansTransactionController: Should be replaced with the corresponding route below
        Route::post('{loanId}/disburse', [LoansController::class, 'disburse']);
        Route::post('{loanId}/restructure', [LoansController::class, 'restructure']);

        Route::post('{loanId}/guarantee', [LoansController::class, 'addGuarantor']);
        Route::post('guarantors/{guarantorId}/respond', [LoansController::class, 'respondToGuarantee']);
    });

    // Shares
    Route::group(['prefix' => 'shares'], function () {
        Route::get('/', [SharesController::class, 'index']);
        // This method action has been revised in a dedicated ShareTransactionController: Should be replaced with the corresponding route below
        Route::post('purchase', [SharesController::class, 'purchase']);
        Route::get('dividends', [SharesController::class, 'getDividends']);
        Route::get('certificates', [SharesController::class, 'getCertificates']); // To be looked into for replacement.
    });

    // Reports
    Route::group(['prefix' => 'reports'], function () {
        Route::get('member-statement', [ReportsController::class, 'memberStatement']);
        Route::get('savings-summary', [ReportsController::class, 'savingsSummary']);
        Route::get('loans-summary', [ReportsController::class, 'loansSummary']);

        // Admin/Staff only reports
        Route::get('financial-summary', [ReportsController::class, 'financialSummary']);
        Route::get('trial-balance', [ReportsController::class, 'trialBalance']);
        Route::get('income-statement', [ReportsController::class, 'incomeStatement']);
        Route::get('balance-sheet', [ReportsController::class, 'balanceSheet']);
        Route::get('member-list', [ReportsController::class, 'memberList']);
        Route::get('loan-portfolio', [ReportsController::class, 'loanPortfolio']);
    });

    /*
     |-----------------------------------------------------------------------------------
     *   DEDICATED TRANSACTIONS ROUTES
     |----------------------------------------------------------------------------------
    */

    // Savings Transactions
    Route::prefix('savings')->group(function () {
        Route::post('deposit', [SavingsTransactionController::class, 'deposit']);
        Route::post('withdrawal', [SavingsTransactionController::class, 'withdrawal']);
        Route::get('balance/{account}', [SavingsTransactionController::class, 'balance']);
        Route::get('history/{account}', [SavingsTransactionController::class, 'history']);
        Route::post('reverse/{transaction}', [SavingsTransactionController::class, 'reverse']);
    });

    // Share Transactions
    Route::prefix('shares')->group(function () {
        Route::post('purchase', [ShareTransactionController::class, 'purchase']);
        Route::get('portfolio/{member}', [ShareTransactionController::class, 'portfolio']);
        Route::get('history/{member}', [ShareTransactionController::class, 'history']);
    });

    // Loan Transactions
    Route::prefix('loans')->group(function () {
        Route::post('disburse', [LoanTransactionController::class, 'disburse']);
        Route::post('repayment', [LoanTransactionController::class, 'repayment']);
        Route::get('schedule/{loan}', [LoanTransactionController::class, 'schedule']);
        Route::get('history/{loan}', [LoanTransactionController::class, 'history']);
        Route::get('summary/{loan}', [LoanTransactionController::class, 'summary']);
    });

    // Wallet Transactions
    Route::prefix('wallet')->group(function () {
        Route::post('topup', [WalletTransactionController::class, 'topup']);
        Route::post('withdrawal', [WalletTransactionController::class, 'withdrawal']);
        Route::post('transfer-to-savings', [WalletTransactionController::class, 'transferToSavings']);
        Route::post('repay-loan', [WalletTransactionController::class, 'repayLoan']);
        Route::get('balance/{account}', [WalletTransactionController::class, 'balance']);
        Route::get('history/{account}', [WalletTransactionController::class, 'history']);
    });

    // General Transaction Routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
        Route::get('member/{member}', [TransactionController::class, 'memberTransactions'])->name('transactions.member');
        Route::get('summary/{member}', [TransactionController::class, 'memberSummary'])->name('transactions.summary');
    });
});
