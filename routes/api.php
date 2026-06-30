<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\SendTwoFactorEmailCodeController;
use App\Http\Controllers\Api\Auth\TwoFactorChallengeController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
 * Mobile token API (Sanctum bearer tokens). Unauthenticated auth endpoints are
 * throttled tightly by IP; authenticated endpoints use the per-user api limit.
 */
Route::middleware('throttle:api-auth')->group(function (): void {
    Route::post('login', LoginController::class)->name('api.login');
    Route::post('two-factor-challenge', TwoFactorChallengeController::class)
        ->name('api.two-factor.challenge');
    Route::post('two-factor-challenge/email-code', SendTwoFactorEmailCodeController::class)
        ->name('api.two-factor.email-code');
});

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
    Route::get('user', UserController::class)->name('api.user');
    Route::post('logout', LogoutController::class)->name('api.logout');
});
