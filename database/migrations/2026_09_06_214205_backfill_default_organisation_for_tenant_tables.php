<?php

use App\Models\Organisation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

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
        'pipeline_states',
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
        $organisation = Organisation::firstOrCreate(
            ['slug' => 'default'],
            ['name' => 'Default', 'slug' => 'default']
        );

        foreach ($this->tables as $table) {
            DB::table($table)
                ->whereNull('organisation_id')
                ->update(['organisation_id' => $organisation->id]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table) {
            DB::table($table)->update(['organisation_id' => null]);
        }
    }
};
