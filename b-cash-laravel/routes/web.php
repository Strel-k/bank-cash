<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return view('welcome');
});

// Temporary debug endpoint to inspect auth/session state from the browser
Route::get('/debug-auth', function (Request $request) {
    return response()->json([
        'auth_check' => auth()->check(),
        'auth_id' => auth()->id(),
        'session_id' => session()->getId(),
        'cookies' => $request->cookies->all(),
        'headers' => [
            'cookie' => $request->headers->get('cookie'),
            'referer' => $request->headers->get('referer')
        ]
    ]);
});

// Web auth pages
Route::get('login', function () {
    return view('auth.login');
})->name('login');

Route::get('register', function () {
    return view('auth.register');
})->name('register');

// Dashboard (requires authentication)
Route::get('dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');

// Auth form handlers
Route::post('login', [AuthController::class, 'login'])->name('login.post');
Route::post('register', [AuthController::class, 'register'])->name('register.post');

// Google Login Routes
Route::get('auth/google', [LoginController::class, 'redirectToGoogle'])
    ->name('login.google');
Route::get('auth/google/callback', [LoginController::class, 'handleGoogleCallback'])
    ->name('login.google.callback');

// Optional: logout route for web (uses AuthController)
Route::post('logout', [AuthController::class, 'logout'])->name('logout');
