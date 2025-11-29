<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Auth;

// 🏠 Root (welcome page)
Route::get('/', function () {
    return view('welcome');
});

// 🧪 Debug endpoint to check session/auth state
Route::get('/debug-auth', function (Request $request) {
    return response()->json([
        'auth_check' => Auth::check(),
        'auth_id' => Auth::id(),
        'session_id' => session()->getId(),
        'cookies' => $request->cookies->all(),
        'headers' => [
            'cookie' => $request->headers->get('cookie'),
            'referer' => $request->headers->get('referer')
        ]
    ]);
});

// 🧪 Test API endpoint
Route::get('/test-api', function () {
    return response()->json(['message' => 'API is working']);
});

// 🧭 Public Auth Views
Route::get('login', function () {
    return view('auth.login');
})->name('login');

Route::get('register', function () {
    return view('auth.register');
})->name('register');

// 🧍 Registration & Login Form Handlers
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::post('register', [AuthController::class, 'register'])->name('register.post');

// 🌐 Google Login Routes
Route::middleware(['web'])->group(function () {
    Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])
        ->name('login.google');

    Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback'])
        ->name('login.google.callback');
});

// 🧱 Dashboard & Wallet Routes (Protected Routes)
Route::middleware(['web', 'auth'])->group(function () {
    // Dashboard Page
    Route::get('/dashboard', function () {
        $user = Auth::user();

        if (!$user) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Authentication required.');
        }

        return view('dashboard')->with('user', $user);
    })->name('dashboard');

    // 🎯 Wallet Routes
    Route::get('/wallet/balance', [WalletController::class, 'getBalance'])->name('wallet.balance');
    Route::post('/wallet/add-money', [WalletController::class, 'addMoney'])->name('wallet.add-money');
    Route::post('/wallet/send-money', [WalletController::class, 'sendMoney'])->name('wallet.send-money');
    Route::post('/wallet/pay-bills', [WalletController::class, 'payBills'])->name('wallet.pay-bills');
    Route::get('/wallet/search-users', [WalletController::class, 'searchUsers'])->name('wallet.search-users');
    
    // 📊 Transaction Routes
    Route::get('/transactions/history', [TransactionController::class, 'getHistory'])->name('transactions.history');
    Route::get('/transactions/stats', [TransactionController::class, 'getStats'])->name('transactions.stats');

    // 🚪 Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
});

// 🛠️ Admin Routes (Optional)
Route::middleware(['web', 'auth'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.index');
    })->middleware('admin')->name('admin.dashboard');
});