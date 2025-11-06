<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WalletController;

Route::post('auth/login', [AuthController::class, 'login']);

// Wallet API (prefixed with /api via RouteServiceProvider)
Route::get('test', function() {
    return response()->json(['message' => 'Test route works!']);
});

// Wallet routes (require authentication in controller)
Route::prefix('wallet')->group(function () {
    Route::get('balance', [WalletController::class, 'getBalance']);
    Route::post('add-money', [WalletController::class, 'addMoney']);
    Route::post('send-money', [WalletController::class, 'sendMoney']);
    Route::post('pay-bills', [WalletController::class, 'payBills']);
    Route::get('search-users', [WalletController::class, 'searchPhoneNumbers']);
});
