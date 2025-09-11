<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authcontroller;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\MembersController;
use App\Http\Controllers\Admin\SavingsController as AdminSavingsController;
use App\Http\Controllers\Admin\LoansController as AdminLoansController;
use App\Http\Controllers\Admin\SharesController as AdminSharesController;
use App\Http\Controllers\Admin\ReportsController as AdminReportsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function(){
    return view('signin');
});

Route::get('/signup', function(){
    return view('signup');
});

Route::get('/home', function(){
    return view('home');
});

Route::post('/signup', [Authcontroller::class , 'signup']);
Route::post('/login', [Authcontroller::class , 'login']);
Route::post('/logout',[Authcontroller::class, 'logout']);

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

    // Protected Admin Routes
    Route::group(['middleware' => ['auth', 'admin']], function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Members Management
        Route::group(['prefix' => 'members', 'as' => 'members.'], function () {
            Route::get('/', [MembersController::class, 'index'])->name('index');
            Route::get('requests', [MembersController::class, 'requests'])->name('requests');
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
            Route::post('manual-transaction', [AdminSavingsController::class, 'manualTransaction'])->name('manual-transaction');
        });

        // Loans Management
        Route::group(['prefix' => 'loans', 'as' => 'loans.'], function () {
            Route::get('/', [AdminLoansController::class, 'index'])->name('index');
            Route::get('{id}', [AdminLoansController::class, 'show'])->name('show');
            Route::post('{id}/approve', [AdminLoansController::class, 'approve'])->name('approve');
            Route::post('{id}/reject', [AdminLoansController::class, 'reject'])->name('reject');
            Route::post('{id}/disburse', [AdminLoansController::class, 'disburse'])->name('disburse');
            Route::get('applications', [AdminLoansController::class, 'applications'])->name('applications');
            Route::get('products', [AdminLoansController::class, 'products'])->name('products');
        });

        // Shares Management
        Route::group(['prefix' => 'shares', 'as' => 'shares.'], function () {
            Route::get('/', [AdminSharesController::class, 'index'])->name('index');
            Route::get('purchases', [AdminSharesController::class, 'purchases'])->name('purchases');
            Route::post('purchases/{id}/approve', [AdminSharesController::class, 'approvePurchase'])->name('purchases.approve');
            Route::get('dividends', [AdminSharesController::class, 'dividends'])->name('dividends');
            Route::post('dividends/declare', [AdminSharesController::class, 'declareDividend'])->name('dividends.declare');
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
        });
    });
});
