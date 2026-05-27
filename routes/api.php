<?php

use App\Http\Controllers\ContactController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware(['web', 'auth:sanctum', 'throttle:api'])->group(function () {
    // Authenticated user endpoint
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Contact Management routes
    Route::prefix('contacts')->name(
        'contacts.'
    )->group(function () {
        Route::post(
            '/bulk/delete',
            [ContactController::class, 'bulkDelete']
        )->name('bulk.delete');

        Route::post(
            '/bulk/restore',
            [ContactController::class, 'bulkRestore']
        )->name('bulk.restore');

        Route::get(
            '/',
            [ContactController::class, 'index']
        )->name('index');
        Route::post(
            '/',
            [ContactController::class, 'store']
        )->name('store');
        Route::get(
            '/{companyContact}',
            [ContactController::class, 'show']
        )->name('show');
        Route::put(
            '/{companyContact}',
            [ContactController::class, 'update']
        )->name('update');
        Route::patch(
            '/{companyContact}',
            [ContactController::class, 'update']
        )->name('patch');
        Route::delete(
            '/{companyContact}',
            [ContactController::class, 'destroy']
        )->name('destroy');

        Route::post(
            '/{id}/restore',
            [ContactController::class, 'restore']
        )->name('restore');

        Route::delete(
            '/{id}/force',
            [ContactController::class, 'forceDelete']
        )->name('force-delete');
    });
});
