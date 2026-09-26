<?php

use App\Models\Activity;
use App\Models\Address;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\DealStatus;
use App\Models\Industry;
use App\Models\InteractionLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceStatus;
use App\Models\Label;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\OrganisationDataExport;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\PipelineStatus;
use App\Models\Post;
use App\Models\Report;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;

return [
    'scoped_models' => [
        Contact::class,
        Company::class,
        Task::class,
        TaskStatus::class,
        Order::class,
        OrderStatus::class,
        Address::class,
        Category::class,
        Post::class,
        Comment::class,
        Invoice::class,
        InvoiceItem::class,
        InvoiceStatus::class,
        Pipeline::class,
        PipelineStage::class,
        PipelineStatus::class,
        Deal::class,
        DealStatus::class,
        Ticket::class,
        TicketPriority::class,
        TicketStatus::class,
        Label::class,
        Activity::class,
        InteractionLog::class,
        Report::class,
        Industry::class,
        OrganisationDataExport::class,
    ],

        /*
    |--------------------------------------------------------------------------
    | Central tables
    |--------------------------------------------------------------------------
    |
    | Tables that are deliberately not scoped to an organisation. Every entry
    | needs a reason. Any table not listed here must have an organisation_id
    | column, or must be added to scoped_models with the trait applied.
    |
    */

    'central_tables' => [
        'migrations', // framework
        'cache', // framework
        'cache_locks', // framework
        'jobs', // framework (tenant carried in the payload)
        'job_batches', // framework
        'failed_jobs', // framework
        'sessions', // framework (user scoped)
        'password_reset_tokens', // framework
        'personal_access_tokens', // Sanctum, user scoped
        'notifications', // morphs to User (notifiable), no organisation_id column - confirmed from the notifications migration
        'users', // users can belong to many organisations
        'organisations', // the tenant table itself
        'organisation_user', // OrganisationMembership pivot table (confirmed via $table = 'organisation_user'); links a user to an organisation, is not itself organisation data
        'permissions', // Spatie, team scoped by its own team_id column
        'roles', // Spatie, team scoped by its own team_id column
        'model_has_permissions', // Spatie
        'model_has_roles', // Spatie
        'role_has_permissions', // Spatie
        'plans', // global Cashier product catalogue - confirmed: Plan has no organisation_id property/column
        'subscriptions', // Cashier - confirmed: keyed on user_id, not organisation_id
        'subscription_items', // Cashier - keyed on subscription_id, not organisation_id
        'settings', // confirmed from app/Models/Setting.php: a single row of platform-wide admin config (site_name, maintenance_mode, two_factor_required, session_timeout_minutes, ...), no organisation_id column at all - not per-organisation data
        'registration_interests', // confirmed from RegistrationInterestController::store()'s own docblock: a public, unauthenticated pre-signup lead capture, created before any organisation or user account exists
        'dashboard_widget_preferences', // confirmed from DashboardWidgetPreferenceController: keyed by user (forUser()/updateForUser()), never touches an organisation
        'custom_dashboard_widgets', // confirmed from CustomDashboardWidgetController::destroy()'s explicit user_id ownership check, not organisation_id
    ],

    /*
    |--------------------------------------------------------------------------
    | Global scope bypass allow-list
    |--------------------------------------------------------------------------
    |
    | Files allowed to call withoutGlobalScope(s). Each entry needs a code
    | comment explaining why the bypass is safe. This is NOT a place to
    | pre-register every App\Services\Organisations\* service "just in
    | case" - see the note below the array.
    |
    */

    'global_scope_bypass_allow_list' => [
        'app/Actions/Organisations/CascadeSoftDeleteOrganisationScopedRecords.php',
        // app/Services/Platform/ReportingService.php is NOT listed here.
        // Its withoutGlobalScope(s) calls are removed entirely in Commit 10
        // rather than allow-listed, because Organisation, User and
        // Subscription are all central_tables that never carry the
        // "organisation" scope in the first place - the calls were dead
        // code, not a genuine bypass.
    ],
];
