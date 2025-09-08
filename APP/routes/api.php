<?php

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
        Route::post('deposit', [SavingsController::class, 'deposit']);
        Route::post('withdraw', [SavingsController::class, 'withdraw']);
        Route::get('products', [SavingsController::class, 'getSavingsProducts']);
        Route::get('accounts/{accountId}/transactions', [SavingsController::class, 'getTransactions']);
    });

    // Loans
    Route::group(['prefix' => 'loans'], function () {
        Route::get('/', [LoansController::class, 'index']);
        Route::post('apply', [LoansController::class, 'apply']);
        Route::get('{loanId}', [LoansController::class, 'show']);
        Route::post('{loanId}/repay', [LoansController::class, 'repay']);
        Route::get('products', [LoansController::class, 'getLoanProducts']);
        Route::get('{loanId}/schedule', [LoansController::class, 'getRepaymentSchedule']);

        // Admin/Staff only
        Route::post('{loanId}/approve', [LoansController::class, 'approve']);
        Route::post('{loanId}/disburse', [LoansController::class, 'disburse']);
        Route::post('{loanId}/restructure', [LoansController::class, 'restructure']);

        // Guarantorship
        Route::post('{loanId}/guarantee', [LoansController::class, 'addGuarantor']);
        Route::post('guarantors/{guarantorId}/respond', [LoansController::class, 'respondToGuarantee']);
    });

    // Shares
    Route::group(['prefix' => 'shares'], function () {
        Route::get('/', [SharesController::class, 'index']);
        Route::post('purchase', [SharesController::class, 'purchase']);
        Route::get('dividends', [SharesController::class, 'getDividends']);
        Route::get('certificates', [SharesController::class, 'getCertificates']);
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

});

// Legacy routes (for backward compatibility)
Route::group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers\Api\V1'], function () {
    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('loans', LoanController::class);
    Route::apiResource('transactions', TransactionController::class);
});
