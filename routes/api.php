<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\EmailVerificationResendController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Test\ApiResponseSandboxController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::middleware('local.only')->group(function () {
        Route::get('/_debug/api-response/success', [ApiResponseSandboxController::class, 'successSample'])
            ->name('api.v1.debug.api-response.success');

        Route::get('/_debug/api-response/error', [ApiResponseSandboxController::class, 'errorSample'])
            ->name('api.v1.debug.api-response.error');
    });

    // ゲスト向け認証ルート
    Route::middleware('guest')->group(function () {
        Route::post('/register', [RegisteredUserController::class, 'store'])
            ->name('api.v1.register');

        Route::post('/login', [AuthenticatedSessionController::class, 'store'])
            ->name('api.v1.login');

        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
            ->name('api.v1.password.send-reset-link');

        Route::post('/reset-password', [NewPasswordController::class, 'store'])
            ->name('api.v1.password.reset');
    });

    // 認証済みユーザー向けルート
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/user', function (Request $request) {
            return $request->user()->load('staffProfile');
        })->name('api.v1.user');

        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
            ->name('api.v1.logout');

        // メール認証ルート（Laravel既定のルート名を維持）
        Route::get('/verify-email/{id}/{hash}', EmailVerificationController::class)
            ->middleware(['signed', 'throttle:6,1'])
            ->name('verification.verify');

        Route::post('/email/verification-notification', [EmailVerificationResendController::class, 'store'])
            ->middleware('throttle:6,1')
            ->name('verification.send');
    });
});
