<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VerificationController;

// User verification routes
Route::middleware(['auth'])->group(function () {
    Route::get('/verification', [VerificationController::class, 'showVerificationPage'])->name('verification');
    Route::post('/api/verification/start', [VerificationController::class, 'startVerification']);
    Route::post('/api/verification/upload-document', [VerificationController::class, 'uploadDocument']);
    Route::post('/api/verification/upload-face', [VerificationController::class, 'uploadFace']);
});

// Admin verification routes
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/verifications', [VerificationController::class, 'showAdminVerifications'])->name('admin.verifications');
    Route::get('/api/admin/verifications', [VerificationController::class, 'listVerifications']);
    Route::post('/api/admin/verifications/{id}/approve', [VerificationController::class, 'approveVerification']);
    Route::post('/api/admin/verifications/{id}/reject', [VerificationController::class, 'rejectVerification']);
    Route::get('/api/admin/verifications/{id}/documents', [VerificationController::class, 'getVerificationDocuments']);
});