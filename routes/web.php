<?php

use App\Http\Controllers\Auth\TwoFactorEmailChallengeController;
use App\Http\Controllers\Auth\TwoFactorEmailCodeController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

// Email-code alternative for the two-factor challenge (no auth yet — mid-login).
Route::post('two-factor/email-code', TwoFactorEmailCodeController::class)
    ->middleware('throttle:two-factor')
    ->name('two-factor.email-code');

Route::post('two-factor/email-challenge', TwoFactorEmailChallengeController::class)
    ->middleware('throttle:two-factor')
    ->name('two-factor.email-challenge');

require __DIR__.'/settings.php';
