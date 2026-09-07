<?php

use App\Http\Controllers\SettingController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'tenant'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Application Settings
    |--------------------------------------------------------------------------
    |
    | These routes manage the application's organisation-scoped settings.
    |
    */

    Route::get('settings', [SettingController::class, 'index'])
        ->name('settings.index');

    Route::get('settings/general', [SettingController::class, 'general'])
        ->name('settings.general');

    Route::put('settings/general', [SettingController::class, 'updateGeneral'])
        ->name('settings.general.update');

    Route::get('settings/system', [SettingController::class, 'system'])
        ->name('settings.system');

    Route::put('settings/system', [SettingController::class, 'updateSystem'])
        ->name('settings.system.update');

    Route::get('settings/security-policy', [SettingController::class, 'securityPolicy'])
        ->name('settings.security-policy');

    Route::put('settings/security', [SettingController::class, 'updateSecurity'])
        ->name('settings.security.update');

    /*
    |--------------------------------------------------------------------------
    | Profile Settings
    |--------------------------------------------------------------------------
    */

    Route::get(
        'settings/profile',
        [ProfileController::class, 'edit']
    )->name('profile.edit');

    Route::patch(
        'settings/profile',
        [ProfileController::class, 'update']
    )->name('profile.update');
});

Route::middleware(['auth', 'verified', 'tenant'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Profile
    |--------------------------------------------------------------------------
    */

    Route::delete(
        'settings/profile',
        [ProfileController::class, 'destroy']
    )->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | Personal Account Security
    |--------------------------------------------------------------------------
    |
    | This is deliberately separate from the application-wide security
    | settings above.
    |
    | GET  /settings/security          -> personal account security page
    | PUT  /settings/password          -> personal password update
    |
    */

    Route::get('settings/security', [SecurityController::class, 'edit'])
        ->middleware(RequirePassword::class)
        ->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

    /*
    |--------------------------------------------------------------------------
    | Appearance
    |--------------------------------------------------------------------------
    */

    Route::inertia(
        'settings/appearance',
        'settings/Appearance'
    )->name('appearance.edit');
});
