<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Public routes
Route::get('test', function() {
    return response()->json(['message' => 'Test route works!']);
});

// Authentication routes
Route::post('auth/login', [AuthController::class, 'login']);

// Google OAuth login
Route::get('auth/google', [LoginController::class, 'redirectToGoogle']);
Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback']);

// Protected routes (require authentication)
Route::middleware(['auth:web'])->group(function () {
    
    // Wallet routes
    Route::prefix('wallet')->group(function () {
        Route::get('balance', [WalletController::class, 'getBalance']);
        Route::post('add-money', [WalletController::class, 'addMoney']);
        Route::post('send-money', [WalletController::class, 'sendMoney']);
        Route::post('pay-bills', [WalletController::class, 'payBills']);
        Route::get('search-users', [WalletController::class, 'searchUsers']);
    });

    // Transaction routes
    Route::prefix('transactions')->group(function () {
        Route::get('history', [TransactionController::class, 'getHistory']);
        Route::get('stats', [TransactionController::class, 'getStats']);
        Route::get('search', [TransactionController::class, 'searchTransactions']);
        Route::get('recent', [TransactionController::class, 'getRecentTransactions']);
        Route::get('by-reference', [TransactionController::class, 'getTransactionByReference']);
    });

});

// Fallback for undefined API routes
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'API endpoint not found'
    ], 404);
});