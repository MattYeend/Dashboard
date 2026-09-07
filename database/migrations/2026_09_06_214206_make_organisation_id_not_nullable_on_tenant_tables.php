<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The tenant-scoped tables backfilled onto the Default organisation.
     *
     * @var list<string>
     */
    protected array $tables = [
        'companies',
        'contacts',
        'addresses',
        'orders',
        'tasks',
        'task_statuses',
        'deals',
        'pipelines',
        'pipeline_stages',
        'deal_statuses',
        'invoices',
        'posts',
        'comments',
        'categories',
        'pipeline_stages',
        'attachments',
        'industries',
        'interaction_logs',
        'invoice_items',
        'invoice_statuses',
        'likes',
        'order_statuses',
        'reports',
        'tags',
        'tickets',
        'ticket_statuses',
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('organisation_id')->nullable(false)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('organisation_id')->nullable()->change();
            });
        }
    }
};
