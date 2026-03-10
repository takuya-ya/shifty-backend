<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/hello', function (Request $request) {
    return response()->json(['message' => 'Hello from Laravel']);
});

Route::prefix('v1')->group(function () {
    // ゲスト向け認証ルート
    Route::middleware('guest')->group(function () {
        Route::post('/register', [RegisteredUserController::class, 'store'])
            ->name('api.v1.auth.register');

        Route::post('/login', [AuthenticatedSessionController::class, 'store'])
            ->name('api.v1.auth.login');

        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name('api.v1.auth.password.email');

        Route::post('/reset-password', [NewPasswordController::class, 'store'])
            ->name('api.v1.auth.password.store');
    });

    // 認証済みユーザー向けルート
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user()->load('staffProfile');
        })->name('api.v1.auth.user');

        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('api.v1.auth.logout');

        // メール認証ルート（Laravel既定のルート名を維持）
        Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
    });
});
