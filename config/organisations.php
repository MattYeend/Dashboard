<?php

return [
    /**
     * Fully-qualified class names of every model scoped to an Organisation
     * via the BelongsToOrganisation trait. Used by DataExportService to
     * build a full data export and by CascadeSoftDeleteOrganisationScopedRecords
     * when an organisation is offboarded.
     */
    'scoped_models' => [
        \App\Models\Contact::class,
        \App\Models\Company::class,
        \App\Models\Task::class,
        \App\Models\TaskStatus::class,
        \App\Models\Order::class,
        \App\Models\OrderStatus::class,
        \App\Models\Address::class,
        \App\Models\Category::class,
        \App\Models\Post::class,
        \App\Models\Comment::class,
        \App\Models\Invoice::class,
        \App\Models\InvoiceItem::class,
        \App\Models\InvoiceStatus::class,
        \App\Models\Pipeline::class,
        \App\Models\PipelineStage::class,
        \App\Models\Deal::class,
        \App\Models\DealStatus::class,
        \App\Models\Ticket::class,
        \App\Models\TicketPriority::class,
        \App\Models\TicketStatus::class,
        \App\Models\Label::class,
        \App\Models\Activity::class,
        \App\Models\InteractionLog::class,
        \App\Models\Report::class,
        \App\Models\Setting::class,
    ],
];