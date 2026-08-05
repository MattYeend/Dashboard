<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardWidgetPreferenceController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\DealStatusController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\InvoiceStatusController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\PipelineStageController;
use App\Http\Controllers\PipelineStatusController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RegistrationInterestController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::inertia('/', 'Welcome')->name('home');

Route::post('stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook');

Route::get('register', fn () => Inertia::render('auth/RegisterInterest'))
    ->name('register');
Route::post('register', [RegistrationInterestController::class, 'store'])
    ->name('register.store');
Route::get('register/thanks', fn () => Inertia::render('auth/RegisterThanks'))
    ->name('register.thanks');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('dashboard/widgets')->name('dashboard.widgets.')->group(function () {
        Route::get('/', [DashboardWidgetPreferenceController::class, 'index'])->name('index');
        Route::put('/', [DashboardWidgetPreferenceController::class, 'update'])->name('update');
    });

    Route::middleware('can:view notifications')->prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/unread', [NotificationController::class, 'unread'])->name('unread');
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    });

    Route::prefix('users')->name('users.')->group(function () {
        Route::post('/bulk/delete', [UserController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [UserController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [UserController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [UserController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [UserController::class, 'export'])->name('export');
        Route::post('/import', [UserController::class, 'import'])->name('import');

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

        Route::get('/export', [TaskStatusController::class, 'export'])->name('export');
        Route::post('/import', [TaskStatusController::class, 'import'])->name('import');

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

        Route::get('/export', [TaskController::class, 'export'])->name('export');
        Route::post('/import', [TaskController::class, 'import'])->name('import');

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

        Route::get('/export', [OrderStatusController::class, 'export'])->name('export');
        Route::post('/import', [OrderStatusController::class, 'import'])->name('import');

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

        Route::get('/export', [OrderController::class, 'export'])->name('export');
        Route::post('/import', [OrderController::class, 'import'])->name('import');

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

        Route::get('/export', [IndustryController::class, 'export'])->name('export');
        Route::post('/import', [IndustryController::class, 'import'])->name('import');

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

        Route::get('/export', [CompanyController::class, 'export'])->name('export');
        Route::post('/import', [CompanyController::class, 'import'])->name('import');

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

        Route::get('/export', [AddressController::class, 'export'])->name('export');
        Route::post('/import', [AddressController::class, 'import'])->name('import');

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

    Route::prefix('categories')->name('categories.')->group(function () {
        Route::post('/bulk/delete', [CategoryController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [CategoryController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [CategoryController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [CategoryController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [CategoryController::class, 'export'])->name('export');
        Route::post('/import', [CategoryController::class, 'import'])->name('import');

        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}', [CategoryController::class, 'show'])->name('show');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('posts')->name('posts.')->group(function () {
        Route::post('/bulk/delete', [PostController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [PostController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [PostController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [PostController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [PostController::class, 'export'])->name('export');
        Route::post('/import', [PostController::class, 'import'])->name('import');

        Route::get('/', [PostController::class, 'index'])->name('index');
        Route::get('/create', [PostController::class, 'create'])->name('create');
        Route::post('/', [PostController::class, 'store'])->name('store');
        Route::get('/{post}', [PostController::class, 'show'])->name('show');
        Route::get('/{post}/edit', [PostController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{post}', [PostController::class, 'update'])->name('update');
        Route::delete('/{post}', [PostController::class, 'destroy'])->name('destroy');

        Route::post('/{post}/like', [PostController::class, 'like'])->name('like');
        Route::delete('/{post}/like', [PostController::class, 'unlike'])->name('unlike');

        Route::prefix('/{post}/comments')->name('comments.')->group(function () {
            Route::post('/bulk/delete', [CommentController::class, 'bulkDelete'])->name('bulk.delete');
            Route::post('/bulk/restore', [CommentController::class, 'bulkRestore'])->name('bulk.restore');
            Route::post('/{id}/restore', [CommentController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force', [CommentController::class, 'forceDelete'])->name('force-delete');

            Route::get('/export', [CommentController::class, 'export'])->name('export');
            Route::post('/import', [CommentController::class, 'import'])->name('import');

            Route::post('/', [CommentController::class, 'store'])->name('store');
            Route::put('/{comment}', [CommentController::class, 'update'])->name('update');
            Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy');
            Route::post('/{comment}/like', [CommentController::class, 'like'])->name('like');
            Route::delete('/{comment}/like', [CommentController::class, 'unlike'])->name('unlike');
        });
    });

    Route::prefix('invoice-statuses')->name('invoice-statuses.')->group(function () {
        Route::post('/bulk/delete', [InvoiceStatusController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [InvoiceStatusController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [InvoiceStatusController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [InvoiceStatusController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [InvoiceStatusController::class, 'export'])->name('export');
        Route::post('/import', [InvoiceStatusController::class, 'import'])->name('import');

        Route::get('/', [InvoiceStatusController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceStatusController::class, 'create'])->name('create');
        Route::post('/', [InvoiceStatusController::class, 'store'])->name('store');
        Route::get('/{invoice_status}', [InvoiceStatusController::class, 'show'])->name('show');
        Route::get('/{invoice_status}/edit', [InvoiceStatusController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{invoice_status}', [InvoiceStatusController::class, 'update'])->name('update');
        Route::delete('/{invoice_status}', [InvoiceStatusController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('tags')->name('tags.')->group(function () {
        Route::post('/bulk/delete', [TagController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [TagController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [TagController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [TagController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [TagController::class, 'export'])->name('export');
        Route::post('/import', [TagController::class, 'import'])->name('import');

        Route::get('/', [TagController::class, 'index'])->name('index');
        Route::get('/create', [TagController::class, 'create'])->name('create');
        Route::post('/', [TagController::class, 'store'])->name('store');
        Route::get('/{tag}', [TagController::class, 'show'])->name('show');
        Route::get('/{tag}/edit', [TagController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{tag}', [TagController::class, 'update'])->name('update');
        Route::delete('/{tag}', [TagController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('registration-interests')->name('registration-interests.')->group(function () {
        Route::post('/bulk/delete', [RegistrationInterestController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [RegistrationInterestController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [RegistrationInterestController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [RegistrationInterestController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [RegistrationInterestController::class, 'export'])->name('export');

        Route::get('/', [RegistrationInterestController::class, 'index'])->name('index');
        Route::get('/{registration_interest}', [RegistrationInterestController::class, 'show'])->name('show');
        Route::delete('/{registration_interest}', [RegistrationInterestController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('invoices')->name('invoices.')->group(function () {
        Route::post('/bulk/delete', [InvoiceController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [InvoiceController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [InvoiceController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [InvoiceController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [InvoiceController::class, 'export'])->name('export');
        Route::post('/import', [InvoiceController::class, 'import'])->name('import');

        Route::post('/{invoice}/send', [InvoiceController::class, 'send'])->name('send');
        Route::post('/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])->name('mark-as-paid');
        Route::post('/{invoice}/mark-as-unpaid', [InvoiceController::class, 'markAsUnpaid'])->name('mark-as-unpaid');
        Route::get('/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('pdf');

        Route::get('/', [InvoiceController::class, 'index'])->name('index');
        Route::get('/create', [InvoiceController::class, 'create'])->name('create');
        Route::post('/', [InvoiceController::class, 'store'])->name('store');
        Route::get('/{invoice}', [InvoiceController::class, 'show'])->name('show');
        Route::get('/{invoice}/edit', [InvoiceController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{invoice}', [InvoiceController::class, 'update'])->name('update');
        Route::delete('/{invoice}', [InvoiceController::class, 'destroy'])->name('destroy');

        Route::prefix('/{invoice}/items')->name('items.')->scopeBindings()->group(function () {
            Route::post('/bulk/delete', [InvoiceItemController::class, 'bulkDelete'])->name('bulk.delete');
            Route::post('/bulk/restore', [InvoiceItemController::class, 'bulkRestore'])->name('bulk.restore');
            Route::post('/{id}/restore', [InvoiceItemController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force', [InvoiceItemController::class, 'forceDelete'])->name('force-delete');

            Route::get('/', [InvoiceItemController::class, 'index'])->name('index');
            Route::get('/create', [InvoiceItemController::class, 'create'])->name('create');
            Route::post('/', [InvoiceItemController::class, 'store'])->name('store');
            Route::get('/{invoiceItem}', [InvoiceItemController::class, 'show'])->name('show');
            Route::get('/{invoiceItem}/edit', [InvoiceItemController::class, 'edit'])->name('edit');
            Route::match(['put', 'patch'], '/{invoiceItem}', [InvoiceItemController::class, 'update'])->name('update');
            Route::delete('/{invoiceItem}', [InvoiceItemController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('pipeline-statuses')->name('pipeline-statuses.')->group(function () {
        Route::post('/bulk/delete', [PipelineStatusController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [PipelineStatusController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [PipelineStatusController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [PipelineStatusController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [PipelineStatusController::class, 'export'])->name('export');
        Route::post('/import', [PipelineStatusController::class, 'import'])->name('import');

        Route::get('/', [PipelineStatusController::class, 'index'])->name('index');
        Route::get('/create', [PipelineStatusController::class, 'create'])->name('create');
        Route::post('/', [PipelineStatusController::class, 'store'])->name('store');
        Route::get('/{pipeline_status}', [PipelineStatusController::class, 'show'])->name('show');
        Route::get('/{pipeline_status}/edit', [PipelineStatusController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{pipeline_status}', [PipelineStatusController::class, 'update'])->name('update');
        Route::delete('/{pipeline_status}', [PipelineStatusController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('pipelines')->name('pipelines.')->group(function () {
        Route::post('/bulk/delete', [PipelineController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [PipelineController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [PipelineController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [PipelineController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [PipelineController::class, 'export'])->name('export');
        Route::post('/import', [PipelineController::class, 'import'])->name('import');

        Route::get('/', [PipelineController::class, 'index'])->name('index');
        Route::get('/create', [PipelineController::class, 'create'])->name('create');
        Route::post('/', [PipelineController::class, 'store'])->name('store');
        Route::get('/{pipeline}', [PipelineController::class, 'show'])->name('show');
        Route::get('/{pipeline}/edit', [PipelineController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{pipeline}', [PipelineController::class, 'update'])->name('update');
        Route::delete('/{pipeline}', [PipelineController::class, 'destroy'])->name('destroy');

        Route::prefix('/{pipeline}/stages')->name('stages.')->scopeBindings()->group(function () {
            Route::post('/bulk/delete', [PipelineStageController::class, 'bulkDelete'])->name('bulk.delete');
            Route::post('/bulk/restore', [PipelineStageController::class, 'bulkRestore'])->name('bulk.restore');
            Route::post('/{id}/restore', [PipelineStageController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force', [PipelineStageController::class, 'forceDelete'])->name('force-delete');

            Route::get('/export', [PipelineStageController::class, 'export'])->name('export');
            Route::post('/import', [PipelineStageController::class, 'import'])->name('import');

            Route::get('/', [PipelineStageController::class, 'index'])->name('index');
            Route::get('/create', [PipelineStageController::class, 'create'])->name('create');
            Route::post('/', [PipelineStageController::class, 'store'])->name('store');
            Route::get('/{stage}', [PipelineStageController::class, 'show'])->name('show');
            Route::get('/{stage}/edit', [PipelineStageController::class, 'edit'])->name('edit');
            Route::match(['put', 'patch'], '/{stage}', [PipelineStageController::class, 'update'])->name('update');
            Route::delete('/{stage}', [PipelineStageController::class, 'destroy'])->name('destroy');
        });
    });

    Route::prefix('deal-statuses')->name('deal-statuses.')->group(function () {
        Route::post('/bulk/delete', [DealStatusController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [DealStatusController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [DealStatusController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [DealStatusController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [DealStatusController::class, 'export'])->name('export');
        Route::post('/import', [DealStatusController::class, 'import'])->name('import');

        Route::get('/', [DealStatusController::class, 'index'])->name('index');
        Route::get('/create', [DealStatusController::class, 'create'])->name('create');
        Route::post('/', [DealStatusController::class, 'store'])->name('store');
        Route::get('/{deal_status}', [DealStatusController::class, 'show'])->name('show');
        Route::get('/{deal_status}/edit', [DealStatusController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{deal_status}', [DealStatusController::class, 'update'])->name('update');
        Route::delete('/{deal_status}', [DealStatusController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('deals')->name('deals.')->group(function () {
        Route::post('/bulk/delete', [DealController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [DealController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [DealController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [DealController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [DealController::class, 'export'])->name('export');
        Route::post('/import', [DealController::class, 'import'])->name('import');

        Route::get('/', [DealController::class, 'index'])->name('index');
        Route::get('/create', [DealController::class, 'create'])->name('create');
        Route::post('/', [DealController::class, 'store'])->name('store');
        Route::get('/{deal}', [DealController::class, 'show'])->name('show');
        Route::get('/{deal}/edit', [DealController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{deal}', [DealController::class, 'update'])->name('update');
        Route::delete('/{deal}', [DealController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__.'/settings.php';
