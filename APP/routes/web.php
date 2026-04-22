<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authcontroller;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MembersController;
use App\Http\Controllers\Admin\SavingsController as AdminSavingsController;
use App\Http\Controllers\Admin\LoansController as AdminLoansController;
use App\Http\Controllers\Admin\LoanProductController as AdminLoanProductController;
use App\Http\Controllers\Admin\SharesController as AdminSharesController;
use App\Http\Controllers\Admin\ReportsController as AdminReportsController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\ExpenseController;
use App\Http\Controllers\Admin\IncomeController;



/*
|--------------------------------------------------------------------------
| Admin Panel Routes
|--------------------------------------------------------------------------
|
| These routes handle the admin panel functionality for SACCO management
|
*/

// Admin Authentication Routes
Route::group(['prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');

    // SACCO selection step (shown only when one email maps to multiple admin accounts)
    Route::get('select-sacco', [AdminAuthController::class, 'showSaccoSelect'])->name('select-sacco');
    Route::post('select-sacco', [AdminAuthController::class, 'completeSaccoSelect'])->name('select-sacco.submit');

    // Protected Admin Routes
    Route::group(['middleware' => ['auth', 'admin', 'admin.tenant']], function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Members Management
        Route::group(['prefix' => 'members', 'as' => 'members.'], function () {
            Route::get('/', [MembersController::class, 'index'])->name('index');
            Route::get('requests', [MembersController::class, 'requests'])->name('requests');
            Route::get('requests/{id}/modal', [MembersController::class, 'requestModal'])->name('requests.modal');
            Route::get('create', [MembersController::class, 'create'])->name('create');
            Route::post('/', [MembersController::class, 'store'])->name('store');
            Route::get('{id}', [MembersController::class, 'show'])->name('show');
            Route::get('{id}/edit', [MembersController::class, 'edit'])->name('edit');
            Route::put('{id}', [MembersController::class, 'update'])->name('update');
            Route::post('{id}/suspend', [MembersController::class, 'suspend'])->name('suspend');
            Route::post('{id}/activate', [MembersController::class, 'activate'])->name('activate');
        });

        // Membership Approval Routes
        Route::group(['prefix' => 'memberships', 'as' => 'memberships.'], function () {
            Route::post('{membership}/approve-level-1', [MembersController::class, 'approve_level_1'])->name('approve-level-1');
            Route::post('{membership}/approve-level-2', [MembersController::class, 'approve_level_2'])->name('approve-level-2');
            Route::post('{membership}/approve-level-3', [MembersController::class, 'approve_level_3'])->name('approve-level-3');
        });

        // Savings Management
        Route::group(['prefix' => 'savings', 'as' => 'savings.'], function () {
            Route::get('/', [AdminSavingsController::class, 'index'])->name('index');
            Route::get('accounts', [AdminSavingsController::class, 'accounts'])->name('accounts');
            Route::get('accounts/{id}', [AdminSavingsController::class, 'showAccount'])->name('accounts.show');
            Route::get('transactions', [AdminSavingsController::class, 'transactions'])->name('transactions');
            Route::get('products', [AdminSavingsController::class, 'products'])->name('products');
            Route::get('products/create', [AdminSavingsController::class, 'createProduct'])->name('products.create');
            Route::post('products', [AdminSavingsController::class, 'storeProduct'])->name('products.store');
            Route::get('products/{id}/edit', [AdminSavingsController::class, 'editProduct'])->name('products.edit');
            Route::put('products/{id}', [AdminSavingsController::class, 'updateProduct'])->name('products.update');
            Route::delete('products/{id}', [AdminSavingsController::class, 'deleteProduct'])->name('products.delete');
            Route::post('manual-transaction', [AdminSavingsController::class, 'manualTransaction'])->name('manual-transaction');
        });

        // Loans Management
        Route::group(['prefix' => 'loans', 'as' => 'loans.'], function () {
            Route::get('/', [AdminLoansController::class, 'index'])->name('index');
            Route::get('create', [AdminLoansController::class, 'create'])->name('create');
            Route::post('/', [AdminLoansController::class, 'store'])->name('store');
            Route::get('applications', [AdminLoansController::class, 'applications'])->name('applications');
            Route::get('products', [AdminLoansController::class, 'products'])->name('products');
            Route::get('{id}', [AdminLoansController::class, 'show'])->name('show');
            Route::post('{id}/approve', [AdminLoansController::class, 'approve'])->name('approve');
            Route::post('{id}/reject', [AdminLoansController::class, 'reject'])->name('reject');
            Route::post('{id}/disburse', [AdminLoansController::class, 'disburse'])->name('disburse');
            Route::get('{id}/repayments', [AdminLoansController::class, 'repayments'])->name('repayments');
            Route::post('{id}/add-repayment', [AdminLoansController::class, 'addRepayment'])->name('add-repayment');
            Route::get('{id}/schedule', [AdminLoansController::class, 'getSchedule'])->name('schedule');
            Route::get('{id}/history', [AdminLoansController::class, 'getHistory'])->name('history');
            Route::get('{id}/summary', [AdminLoansController::class, 'getSummary'])->name('summary');
        });

        // Loan Products Management
        Route::group(['prefix' => 'loan-products', 'as' => 'loan-products.'], function () {
            Route::post('/', [AdminLoanProductController::class, 'store'])->name('store');
            Route::put('{id}', [AdminLoanProductController::class, 'update'])->name('update');
            Route::post('{id}/activate', [AdminLoanProductController::class, 'activate'])->name('activate');
            Route::post('{id}/deactivate', [AdminLoanProductController::class, 'deactivate'])->name('deactivate');
        });

        // Shares Management
        Route::group(['prefix' => 'shares', 'as' => 'shares.'], function () {
            Route::get('/', [AdminSharesController::class, 'index'])->name('index');
            Route::get('purchases', [AdminSharesController::class, 'purchases'])->name('purchases');
            Route::post('purchases/{id}/approve', [AdminSharesController::class, 'approvePurchase'])->name('purchases.approve');
            Route::get('dividends', [AdminSharesController::class, 'dividends'])->name('dividends');
            Route::post('dividends/declare', [AdminSharesController::class, 'declareDividend'])->name('dividends.declare');
        });

        // Transactions Management
        Route::group(['prefix' => 'transactions', 'as' => 'transactions.'], function () {
            Route::get('/', [\App\Http\Controllers\Admin\TransactionsController::class, 'index'])->name('index');
            Route::post('process', [\App\Http\Controllers\Admin\TransactionsController::class, 'process'])->name('process');
            Route::get('stats', [\App\Http\Controllers\Admin\TransactionsController::class, 'stats'])->name('stats');
            Route::get('general-ledger', [\App\Http\Controllers\Admin\TransactionsController::class, 'generalLedger'])->name('general-ledger');
            Route::get('trial-balance', [\App\Http\Controllers\Admin\TransactionsController::class, 'trialBalance'])->name('trial-balance');
            Route::get('export', [\App\Http\Controllers\Admin\TransactionsController::class, 'export'])->name('export');
            Route::get('{id}', [\App\Http\Controllers\Admin\TransactionsController::class, 'show'])->name('show');
            Route::post('{id}/approve', [\App\Http\Controllers\Admin\TransactionsController::class, 'approve'])->name('approve');
            Route::post('{id}/reject', [\App\Http\Controllers\Admin\TransactionsController::class, 'reject'])->name('reject');
            Route::post('{id}/reverse', [\App\Http\Controllers\Admin\TransactionsController::class, 'reverse'])->name('reverse');
        });

        // Reports
        Route::group(['prefix' => 'reports', 'as' => 'reports.'], function () {
            Route::get('/', [AdminReportsController::class, 'index'])->name('index');
            Route::get('members', [AdminReportsController::class, 'membersReport'])->name('members');
            Route::get('savings', [AdminReportsController::class, 'savingsReport'])->name('savings');
            Route::get('loans', [AdminReportsController::class, 'loansReport'])->name('loans');
            Route::get('financial', [AdminReportsController::class, 'financialReport'])->name('financial');
            Route::get('trial-balance', [AdminReportsController::class, 'trialBalance'])->name('trial-balance');
            Route::get('balance-sheet', [AdminReportsController::class, 'balanceSheet'])->name('balance-sheet');
            // Phase 2 — expense, income, and profit & loss reports
            Route::get('expenses', [AdminReportsController::class, 'expenseReport'])->name('expenses');
            Route::get('incomes', [AdminReportsController::class, 'incomeReport'])->name('incomes');
            Route::get('profit-loss', [AdminReportsController::class, 'profitLoss'])->name('profit-loss');
        });

        // Tenant Management (Super Admin Only)
        Route::group(['prefix' => 'tenants', 'as' => 'tenants.', 'middleware' => 'super_admin'], function () {
            Route::get('/', [\App\Http\Controllers\Admin\TenantController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Admin\TenantController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\TenantController::class, 'store'])->name('store');
            Route::get('{tenant}', [\App\Http\Controllers\Admin\TenantController::class, 'show'])->name('show');
            Route::get('{tenant}/edit', [\App\Http\Controllers\Admin\TenantController::class, 'edit'])->name('edit');
            Route::put('{tenant}', [\App\Http\Controllers\Admin\TenantController::class, 'update'])->name('update');
            Route::post('switch', [\App\Http\Controllers\Admin\TenantController::class, 'switchTenant'])->name('switch');
        });

        // Staff & Role Management (SACCO admin + super admin only)
        Route::group(['prefix' => 'staff', 'as' => 'staff.'], function () {
            Route::get('/', [StaffController::class, 'index'])->name('index');
            Route::get('create', [StaffController::class, 'create'])->name('create');
            Route::post('/', [StaffController::class, 'store'])->name('store');
            Route::get('{user}/edit', [StaffController::class, 'edit'])->name('edit');
            Route::put('{user}', [StaffController::class, 'update'])->name('update');
            Route::patch('{user}/promote', [StaffController::class, 'promote'])->name('promote');
            Route::patch('{user}/demote', [StaffController::class, 'demote'])->name('demote');
        });

        // Phase 2 — Expenses (feature-flagged: financial.enable_expense_transactions)
        Route::group(['prefix' => 'expenses', 'as' => 'expenses.'], function () {
            Route::get('/', [ExpenseController::class, 'index'])->name('index');
            Route::get('create', [ExpenseController::class, 'create'])->name('create');
            Route::post('/', [ExpenseController::class, 'store'])->name('store');
            Route::get('{id}', [ExpenseController::class, 'show'])->name('show');
            Route::get('{id}/receipt', [ExpenseController::class, 'receipt'])->name('receipt');
        });

        // Phase 2 — Income (feature-flagged: financial.enable_income_transactions)
        Route::group(['prefix' => 'incomes', 'as' => 'incomes.'], function () {
            Route::get('/', [IncomeController::class, 'index'])->name('index');
            Route::get('create', [IncomeController::class, 'create'])->name('create');
            Route::post('/', [IncomeController::class, 'store'])->name('store');
            Route::get('{id}', [IncomeController::class, 'show'])->name('show');
            Route::get('{id}/receipt', [IncomeController::class, 'receipt'])->name('receipt');
        });
    });
});
