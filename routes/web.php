<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook');

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

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/orderable-options', [OrderController::class, 'orderableOptions'])->name('orderable-options');

        Route::post('/bulk/delete', [OrderController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [OrderController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [OrderController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [OrderController::class, 'forceDelete'])->name('force-delete');

        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{order}', [OrderController::class, 'update'])->name('update');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('industries')->name('industries.')->group(function () {
        Route::post('/bulk/delete', [IndustryController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [IndustryController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [IndustryController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [IndustryController::class, 'forceDelete'])->name('force-delete');

        Route::get('/', [IndustryController::class, 'index'])->name('index');
        Route::get('/create', [IndustryController::class, 'create'])->name('create');
        Route::post('/', [IndustryController::class, 'store'])->name('store');
        Route::get('/{industry}', [IndustryController::class, 'show'])->name('show');
        Route::get('/{industry}/edit', [IndustryController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{industry}', [IndustryController::class, 'update'])->name('update');
        Route::delete('/{industry}', [IndustryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('companies')->name('companies.')->group(function () {
        Route::post('/bulk/delete', [CompanyController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [CompanyController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [CompanyController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [CompanyController::class, 'forceDelete'])->name('force-delete');

        Route::get('/', [CompanyController::class, 'index'])->name('index');
        Route::get('/create', [CompanyController::class, 'create'])->name('create');
        Route::post('/', [CompanyController::class, 'store'])->name('store');
        Route::get('/{company}', [CompanyController::class, 'show'])->name('show');
        Route::get('/{company}/edit', [CompanyController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{company}', [CompanyController::class, 'update'])->name('update');
        Route::delete('/{company}', [CompanyController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('plans')->name('plans.')->group(function () {
        Route::post('/bulk/delete', [PlanController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [PlanController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [PlanController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [PlanController::class, 'forceDelete'])->name('force-delete');

        Route::get('/', [PlanController::class, 'index'])->name('index');
        Route::get('/create', [PlanController::class, 'create'])->name('create');
        Route::post('/', [PlanController::class, 'store'])->name('store');
        Route::get('/{plan}', [PlanController::class, 'show'])->name('show');
        Route::get('/{plan}/edit', [PlanController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{plan}', [PlanController::class, 'update'])->name('update');
        Route::delete('/{plan}', [PlanController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('addresses')->name('addresses.')->group(function () {
        Route::get('/addressable-options', [AddressController::class, 'addressableOptions'])->name('addressable-options');

        Route::post('/bulk/delete', [AddressController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [AddressController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [AddressController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [AddressController::class, 'forceDelete'])->name('force-delete');

        Route::get('/', [AddressController::class, 'index'])->name('index');
        Route::get('/create', [AddressController::class, 'create'])->name('create');
        Route::post('/', [AddressController::class, 'store'])->name('store');
        Route::get('/{address}', [AddressController::class, 'show'])->name('show');
        Route::get('/{address}/edit', [AddressController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{address}', [AddressController::class, 'update'])->name('update');
        Route::delete('/{address}', [AddressController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('api-tokens')->name('api-tokens.')->group(function () {
        Route::get('/', [ApiTokenController::class, 'index'])->name('index');
        Route::post('/', [ApiTokenController::class, 'store'])->name('store');
        Route::put('/{apiToken}', [ApiTokenController::class, 'update'])->name('update');
        Route::delete('/{apiToken}', [ApiTokenController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/settings.php';