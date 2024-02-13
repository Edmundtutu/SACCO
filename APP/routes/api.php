<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Authcontroller;
use App\Http\Controllers\Api\V1\TransactionController;



//for the first version api/v1
Route::group(['prefix'=>'v1', 'namespace'=>'App\Http\Controllers\Api\V1' ], function(){  //,'middleware'=>'auth:api'
    Route::apiResource('members', MemberController::class);
    Route::apiResource('accounts', AccountController::class);
    Route::apiResource('loans', LoanController::class);
    Route::apiResource('transactions', TransactionController::class);
    
});

// routes for auth and api security
Route::group(['prefix'=>'auth', 'namespace'=> 'App\Http\Controllers\Api'], function () {
    Route::post('login', [Authcontroller::class, 'login']);
    Route::post('logout',  [Authcontroller::class, 'logout']);
    Route::post('refresh',  [Authcontroller::class, 'refresh']);
});
