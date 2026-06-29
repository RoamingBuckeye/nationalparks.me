<?php

use App\Http\Controllers\Auth\TwoFactorEmailChallengeController;
use App\Http\Controllers\Auth\TwoFactorEmailCodeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\ParkController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\SharedListController;
use App\Http\Controllers\ToggleVisitPoiController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

// Public, read-only shared list (token-gated, no auth).
Route::get('s/{token}', SharedListController::class)->name('shared.show');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('parks', [ParkController::class, 'index'])->name('parks.index');
    Route::get('parks/{park}', [ParkController::class, 'show'])->name('parks.show');

    Route::get('map', MapController::class)->name('map');

    Route::post('visits', [VisitController::class, 'store'])->name('visits.store');
    Route::get('visits/{visit}', [VisitController::class, 'show'])->name('visits.show');
    Route::patch('visits/{visit}', [VisitController::class, 'update'])->name('visits.update');
    Route::delete('visits/{visit}', [VisitController::class, 'destroy'])->name('visits.destroy');

    Route::post('visits/{visit}/pois/{pointOfInterest}', ToggleVisitPoiController::class)
        ->name('visits.pois.toggle');

    Route::post('visits/{visit}/photos', [PhotoController::class, 'store'])->name('visits.photos.store');
    Route::get('photos/{photo}', [PhotoController::class, 'show'])->name('photos.show');
    Route::delete('photos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');
});

// Email-code alternative for the two-factor challenge (no auth yet — mid-login).
Route::post('two-factor/email-code', TwoFactorEmailCodeController::class)
    ->middleware('throttle:two-factor')
    ->name('two-factor.email-code');

Route::post('two-factor/email-challenge', TwoFactorEmailChallengeController::class)
    ->middleware('throttle:two-factor')
    ->name('two-factor.email-challenge');

require __DIR__.'/settings.php';
