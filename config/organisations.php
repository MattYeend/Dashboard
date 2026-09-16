<?php

use App\Models\Activity;
use App\Models\Address;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\DealStatus;
use App\Models\InteractionLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceStatus;
use App\Models\Label;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\Post;
use App\Models\Report;
use App\Models\Setting;
use App\Models\Task;
use App\Models\TaskStatus;
use App\Models\Ticket;
use App\Models\TicketPriority;
use App\Models\TicketStatus;

return [
    /**
     * Fully-qualified class names of every model scoped to an Organisation
     * via the BelongsToOrganisation trait. Used by DataExportService to
     * build a full data export and by CascadeSoftDeleteOrganisationScopedRecords
     * when an organisation is offboarded.
     */
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
        Deal::class,
        DealStatus::class,
        Ticket::class,
        TicketPriority::class,
        TicketStatus::class,
        Label::class,
        Activity::class,
        InteractionLog::class,
        Report::class,
        Setting::class,
    ],
];
