<?php

use App\Http\Controllers\ContactController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    Route::prefix('users')->name('users.')->group(function () {
        Route::post('/bulk/delete', [UserController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [UserController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [UserController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [UserController::class, 'forceDelete'])->name('force-delete');

        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('contacts')->name('contacts.')->group(function () {
        Route::get('/contactable-options', [ContactController::class, 'contactableOptions'])->name('contactable-options');

        Route::post('/bulk/delete', [ContactController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [ContactController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [ContactController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [ContactController::class, 'forceDelete'])->name('force-delete');

        Route::get('/', [ContactController::class, 'index'])->name('index');
        Route::get('/create', [ContactController::class, 'create'])->name('create');
        Route::post('/', [ContactController::class, 'store'])->name('store');
        Route::get('/{contact}', [ContactController::class, 'show'])->name('show');
        Route::get('/{contact}/edit', [ContactController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{contact}', [ContactController::class, 'update'])->name('update');
        Route::delete('/{contact}', [ContactController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('task-statuses')->name('task-statuses.')->group(function () {
        Route::post('/bulk/delete', [TaskStatusController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [TaskStatusController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [TaskStatusController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [TaskStatusController::class, 'forceDelete'])->name('force-delete');

        Route::get('/', [TaskStatusController::class, 'index'])->name('index');
        Route::get('/create', [TaskStatusController::class, 'create'])->name('create');
        Route::post('/', [TaskStatusController::class, 'store'])->name('store');
        Route::get('/{task_status}', [TaskStatusController::class, 'show'])->name('show');
        Route::get('/{task_status}/edit', [TaskStatusController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{task_status}', [TaskStatusController::class, 'update'])->name('update');
        Route::delete('/{task_status}', [TaskStatusController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::post('/bulk/delete', [TaskController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [TaskController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [TaskController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [TaskController::class, 'forceDelete'])->name('force-delete');

        Route::get('/', [TaskController::class, 'index'])->name('index');
        Route::get('/create', [TaskController::class, 'create'])->name('create');
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::get('/{task}', [TaskController::class, 'show'])->name('show');
        Route::get('/{task}/edit', [TaskController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('order-statuses')->name('order-statuses.')->group(function () {
        Route::post('/bulk/delete', [OrderStatusController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [OrderStatusController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [OrderStatusController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [OrderStatusController::class, 'forceDelete'])->name('force-delete');

        Route::get('/', [OrderStatusController::class, 'index'])->name('index');
        Route::get('/create', [OrderStatusController::class, 'create'])->name('create');
        Route::post('/', [OrderStatusController::class, 'store'])->name('store');
        Route::get('/{order_status}', [OrderStatusController::class, 'show'])->name('show');
        Route::get('/{order_status}/edit', [OrderStatusController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{order_status}', [OrderStatusController::class, 'update'])->name('update');
        Route::delete('/{order_status}', [OrderStatusController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/settings.php';
