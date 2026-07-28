<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table => columns to index, per issue #210.
     * Base set is [created_at, updated_at, restored_at, deleted_at]; some
     * tables exclude specific columns as noted in the issue.
     */
    protected array $tables = [
        'categories' => ['created_at', 'updated_at', 'restored_at'],
        'posts' => ['updated_at', 'restored_at'],
        'invoice_status' => ['updated_at', 'restored_at'],
        'comments' => ['created_at', 'updated_at', 'restored_at'],
        'tags' => ['created_at', 'updated_at', 'restored_at', 'deleted_at'],
        'registration_interests' => ['updated_at', 'restored_at', 'deleted_at'],
        'invoices' => ['created_at', 'updated_at', 'restored_at', 'deleted_at'],
        'invoice_items' => ['created_at', 'updated_at', 'restored_at'],
        'pipeline_statuses' => ['updated_at', 'restored_at'],
        'pipelines' => ['updated_at', 'restored_at'],
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach ($this->tables as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        $blueprint->index($column);
                    }
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->tables as $table => $columns) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($table, $columns) {
                foreach ($columns as $column) {
                    if (Schema::hasColumn($table, $column)) {
                        $blueprint->dropIndex([$column]);
                    }
                }
            });
        }
    }
};
