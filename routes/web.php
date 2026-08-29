<?php

use App\Http\Controllers\ActivityController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CustomDashboardWidgetController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardWidgetPreferenceController;
use App\Http\Controllers\DealController;
use App\Http\Controllers\DealStatusController;
use App\Http\Controllers\IndustryController;
use App\Http\Controllers\InteractionLogController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceItemController;
use App\Http\Controllers\InvoiceStatusController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\NotificationBroadcastController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderStatusController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\PipelineStageController;
use App\Http\Controllers\PipelineStatusController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\RegistrationInterestController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\TicketPriorityController;
use App\Http\Controllers\TicketStatusController;
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
    Route::get('dashboard', [DashboardController::class, 'index'])
        ->middleware('can:view dashboard')
        ->name('dashboard');

    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/statistics', [DashboardController::class, 'statistics'])
            ->middleware('can:view statistics')
            ->name('statistics');

        Route::get('/charts', [DashboardController::class, 'charts'])
            ->middleware('can:view charts')
            ->name('charts');

        Route::get('/export', [DashboardController::class, 'export'])
            ->middleware('can:export dashboard data')
            ->name('export-data');
    });

    Route::prefix('dashboard/widgets')->name('dashboard.widgets.')->group(function () {
        Route::get('/', [DashboardWidgetPreferenceController::class, 'index'])->name('index');
        Route::put('/', [DashboardWidgetPreferenceController::class, 'update'])->name('update');
    });

    Route::prefix('dashboard/custom-widgets')->name('dashboard.custom-widgets.')->group(function () {
        Route::get('/metrics', [CustomDashboardWidgetController::class, 'metrics'])->name('metrics');
        Route::post('/', [CustomDashboardWidgetController::class, 'store'])->name('store');
        Route::put('/{customDashboardWidget}', [CustomDashboardWidgetController::class, 'update'])->name('update');
        Route::delete('/{customDashboardWidget}', [CustomDashboardWidgetController::class, 'destroy'])->name('destroy');
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

        Route::get('/export', [ContactController::class, 'export'])->name('export');
        Route::post('/import', [ContactController::class, 'import'])->name('import');

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

            Route::get('/export', [InvoiceItemController::class, 'export'])->name('export');
            Route::post('/import', [InvoiceItemController::class, 'import'])->name('import');

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

    Route::prefix('ticket-statuses')->name('ticket-statuses.')->group(function () {
        Route::post('/bulk/delete', [TicketStatusController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [TicketStatusController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [TicketStatusController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [TicketStatusController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [TicketStatusController::class, 'export'])->name('export');
        Route::post('/import', [TicketStatusController::class, 'import'])->name('import');

        Route::get('/', [TicketStatusController::class, 'index'])->name('index');
        Route::get('/create', [TicketStatusController::class, 'create'])->name('create');
        Route::post('/', [TicketStatusController::class, 'store'])->name('store');
        Route::get('/{ticket_status}', [TicketStatusController::class, 'show'])->name('show');
        Route::get('/{ticket_status}/edit', [TicketStatusController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{ticket_status}', [TicketStatusController::class, 'update'])->name('update');
        Route::delete('/{ticket_status}', [TicketStatusController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('ticket-priorities')->name('ticket-priorities.')->group(function () {
        Route::post('/bulk/delete', [TicketPriorityController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [TicketPriorityController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [TicketPriorityController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [TicketPriorityController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [TicketPriorityController::class, 'export'])->name('export');
        Route::post('/import', [TicketPriorityController::class, 'import'])->name('import');

        Route::get('/', [TicketPriorityController::class, 'index'])->name('index');
        Route::get('/create', [TicketPriorityController::class, 'create'])->name('create');
        Route::post('/', [TicketPriorityController::class, 'store'])->name('store');
        Route::get('/{ticket_priority}', [TicketPriorityController::class, 'show'])->name('show');
        Route::get('/{ticket_priority}/edit', [TicketPriorityController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{ticket_priority}', [TicketPriorityController::class, 'update'])->name('update');
        Route::delete('/{ticket_priority}', [TicketPriorityController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('comments')->name('comments.')->group(function () {
        Route::get('/commentable-options', [CommentController::class, 'commentableOptions'])->name('commentable-options');

        Route::post('/bulk/delete', [CommentController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [CommentController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [CommentController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [CommentController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [CommentController::class, 'export'])->name('export');
        Route::post('/import', [CommentController::class, 'import'])->name('import');

        Route::get('/', [CommentController::class, 'index'])->name('index');
        Route::post('/', [CommentController::class, 'store'])->name('store');
        Route::get('/{comment}', [CommentController::class, 'show'])->name('show');
        Route::match(['put', 'patch'], '/{comment}', [CommentController::class, 'update'])->name('update');
        Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy');

        Route::post('/{comment}/like', [CommentController::class, 'like'])->name('like');
        Route::delete('/{comment}/like', [CommentController::class, 'unlike'])->name('unlike');
    });

    Route::prefix('labels')->name('labels.')->group(function () {
        Route::post('/bulk/delete', [LabelController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [LabelController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [LabelController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [LabelController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [LabelController::class, 'export'])->name('export');
        Route::post('/import', [LabelController::class, 'import'])->name('import');

        Route::get('/', [LabelController::class, 'index'])->name('index');
        Route::get('/create', [LabelController::class, 'create'])->name('create');
        Route::post('/', [LabelController::class, 'store'])->name('store');
        Route::get('/{label}', [LabelController::class, 'show'])->name('show');
        Route::get('/{label}/edit', [LabelController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{label}', [LabelController::class, 'update'])->name('update');
        Route::delete('/{label}', [LabelController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::post('/bulk/delete', [TicketController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [TicketController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [TicketController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [TicketController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [TicketController::class, 'export'])->name('export');
        Route::post('/import', [TicketController::class, 'import'])->name('import');

        Route::get('/', [TicketController::class, 'index'])->name('index');
        Route::get('/create', [TicketController::class, 'create'])->name('create');
        Route::post('/', [TicketController::class, 'store'])->name('store');
        Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
        Route::get('/{ticket}/edit', [TicketController::class, 'edit'])->name('edit');
        Route::match(['put', 'patch'], '/{ticket}', [TicketController::class, 'update'])->name('update');
        Route::post('/{ticket}/resolve', [TicketController::class, 'resolve'])->name('resolve');
        Route::post('/{ticket}/unresolve', [TicketController::class, 'unresolve'])->name('unresolve');
        Route::delete('/{ticket}', [TicketController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('activities')->name('activities.')->group(function () {
        Route::post('/bulk/delete', [ActivityController::class, 'bulkDelete'])->name('bulk.delete');
        Route::post('/bulk/restore', [ActivityController::class, 'bulkRestore'])->name('bulk.restore');
        Route::post('/{id}/restore', [ActivityController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [ActivityController::class, 'forceDelete'])->name('force-delete');

        Route::get('/export', [ActivityController::class, 'export'])->name('export');

        Route::get('/', [ActivityController::class, 'index'])->name('index');
        Route::post('/', [ActivityController::class, 'store'])->name('store');
        Route::match(['put', 'patch'], '/{activity}', [ActivityController::class, 'update'])->name('update');
        Route::delete('/{activity}', [ActivityController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('interaction-logs')->name('interaction-logs.')->group(function () {
        Route::delete('/{id}/force', [InteractionLogController::class, 'forceDelete'])->name('force-delete');
        Route::post('/', [InteractionLogController::class, 'store'])->name('store');
        Route::match(['put', 'patch'], '/{interaction_log}', [InteractionLogController::class, 'update'])->name('update');
        Route::delete('/{interaction_log}', [InteractionLogController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('system')->name('system.')->group(function () {
        Route::get('/', [SystemController::class, 'index'])
            ->middleware('can:view system info')
            ->name('index');

        Route::post('/cache/clear', [SystemController::class, 'clearCache'])
            ->middleware('can:clear cache')
            ->name('cache.clear');

        Route::post('/maintenance/enable', [SystemController::class, 'enableMaintenance'])
            ->middleware('can:run maintenance')
            ->name('maintenance.enable');

        Route::post('/maintenance/disable', [SystemController::class, 'disableMaintenance'])
            ->middleware('can:run maintenance')
            ->name('maintenance.disable');
    });

    Route::prefix('backups')->name('backups.')->group(function () {
        Route::get('/', [BackupController::class, 'index'])
            ->middleware('can:view backups')
            ->name('index');

        Route::post('/', [BackupController::class, 'store'])
            ->middleware('can:create backups')
            ->name('store');

        Route::post('/upload', [BackupController::class, 'upload'])
            ->middleware('can:import backups')
            ->name('upload');

        Route::get('/{filename}/download', [BackupController::class, 'download'])
            ->where('filename', '[A-Za-z0-9_\-\.]+\.zip')
            ->middleware('can:export backups')
            ->name('download');

        Route::post('/{filename}/restore', [BackupController::class, 'restore'])
            ->where('filename', '[A-Za-z0-9_\-\.]+\.zip')
            ->middleware('can:restore backups')
            ->name('restore');

        Route::delete('/{filename}', [BackupController::class, 'destroy'])
            ->where('filename', '[A-Za-z0-9_\-\.]+\.zip')
            ->middleware('can:delete backups')
            ->name('destroy');
    });

    Route::get('/search', [SearchController::class, 'index'])
        ->middleware('throttle:30,1')
        ->name('search');

    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/', [CalendarController::class, 'index'])->name('index');
        Route::get('/events', [CalendarController::class, 'events'])->name('events');
    });

    Route::prefix('attachments')->name('attachments.')->group(function () {
        Route::post('/', [AttachmentController::class, 'store'])->name('store');
        Route::get('/{attachment}/download', [AttachmentController::class, 'download'])->name('download');
        Route::delete('/{attachment}', [AttachmentController::class, 'destroy'])->name('destroy');
    });

    Route::middleware('can:view notifications')->prefix('notification-broadcasts')->name('notification-broadcasts.')->group(function () {
        Route::get('/', [NotificationBroadcastController::class, 'index'])->name('index');
        Route::get('/{notification_broadcast}', [NotificationBroadcastController::class, 'show'])->name('show');

        Route::middleware('can:create notifications')->group(function () {
            Route::get('/create', [NotificationBroadcastController::class, 'create'])->name('create');
            Route::post('/', [NotificationBroadcastController::class, 'store'])->name('store');
        });

        Route::middleware('can:edit notifications')->group(function () {
            Route::get('/{notification_broadcast}/edit', [NotificationBroadcastController::class, 'edit'])->name('edit');
            Route::match(['put', 'patch'], '/{notification_broadcast}', [NotificationBroadcastController::class, 'update'])->name('update');
        });

        Route::middleware('can:delete notifications')->group(function () {
            Route::post('/bulk/delete', [NotificationBroadcastController::class, 'bulkDelete'])->name('bulk.delete');
            Route::post('/bulk/restore', [NotificationBroadcastController::class, 'bulkRestore'])->name('bulk.restore');
            Route::post('/{id}/restore', [NotificationBroadcastController::class, 'restore'])->name('restore');
            Route::delete('/{id}/force', [NotificationBroadcastController::class, 'forceDelete'])->name('force-delete');
            Route::delete('/{notification_broadcast}', [NotificationBroadcastController::class, 'destroy'])->name('destroy');
        });

        Route::middleware('can:send notifications')->group(function () {
            Route::post('/{notification_broadcast}/send', [NotificationBroadcastController::class, 'send'])->name('send');
        });
    });
});

require __DIR__.'/settings.php';
