<?php

use App\Http\Controllers\Api\Transactions\LoanTransactionController;
use App\Http\Controllers\Api\Transactions\SavingsTransactionController;
use App\Http\Controllers\Api\Transactions\ShareTransactionController;
use App\Http\Controllers\Api\Transactions\TransactionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SavingsController;
use App\Http\Controllers\Api\LoansController;
use App\Http\Controllers\Api\SharesController;
use App\Http\Controllers\Api\ReportsController;

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

    // Savings & Accounts
    Route::group(['prefix' => 'savings'], function () {
        Route::get('accounts', [SavingsController::class, 'getAccounts']);
        Route::get('products', [SavingsController::class, 'getSavingsProducts']);
        Route::get('accounts/{accountId}/transactions', [SavingsController::class, 'getTransactions']);
    });

    // Loans
    Route::group(['prefix' => 'loans'], function () {
        Route::get('/', [LoansController::class, 'index']);
        Route::post('apply', [LoansController::class, 'apply']);
        Route::get('{loanId}', [LoansController::class, 'show']);
        // This method action has been revised in a dedicated LoansTransactionController: Should be replaced with the corresponding route below
        Route::post('{loanId}/repay', [LoansController::class, 'repay']);
        Route::get('products', [LoansController::class, 'getLoanProducts']);
        // This method action has been revised in a dedicated LoansTransactionController: Should be replaced with the corresponding route below
        Route::get('{loanId}/schedule', [LoansController::class, 'getRepaymentSchedule']);

        // Admin/Staff only
        Route::post('{loanId}/approve', [LoansController::class, 'approve']);
        // This method action has been revised in a dedicated LoansTransactionController: Should be replaced with the corresponding route below
        Route::post('{loanId}/disburse', [LoansController::class, 'disburse']);
        Route::post('{loanId}/restructure', [LoansController::class, 'restructure']);

        // Guarantorship
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

    // General Transaction Routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionController::class, 'index']);
        Route::get('{transaction}', [TransactionController::class, 'show']);
        Route::get('member/{member}', [TransactionController::class, 'memberTransactions']);
        Route::get('summary/{member}', [TransactionController::class, 'memberSummary']);
    });
});

// Legacy routes (for backward compatibility)
Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\Api\V1'], function () {
    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('loans', LoanController::class);
    Route::apiResource('transactions', TransactionController::class);
});
