<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('tickets', 'title')) {
                $table->string('title')->after('id');
            }

            if (! Schema::hasColumn('tickets', 'description')) {
                $table->text('description')->nullable()->after('title');
            }

            if (! Schema::hasColumn('tickets', 'ticket_status_id')) {
                $table->foreignId('ticket_status_id')->nullable()->constrained('ticket_statuses')->nullOnDelete()->after('description');
            }

            if (! Schema::hasColumn('tickets', 'ticket_priority_id')) {
                $table->foreignId('ticket_priority_id')->nullable()->constrained('ticket_priorities')->nullOnDelete()->after('ticket_status_id');
            }

            if (! Schema::hasColumn('tickets', 'assigned_to')) {
                $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete()->after('ticket_priority_id');
            }

            if (! Schema::hasColumn('tickets', 'due_date')) {
                $table->date('due_date')->nullable()->after('assigned_to');
            }

            if (! Schema::hasColumn('tickets', 'resolved_at')) {
                $table->timestamp('resolved_at')->nullable()->after('due_date');
            }

            if (! Schema::hasColumn('tickets', 'meta')) {
                $table->json('meta')->nullable()->after('resolved_at');
            }

            if (! Schema::hasColumn('tickets', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('meta');
            }

            if (! Schema::hasColumn('tickets', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            }

            if (! Schema::hasColumn('tickets', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete()->after('updated_by');
            }

            if (! Schema::hasColumn('tickets', 'restored_by')) {
                $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete()->after('deleted_by');
            }

            if (! Schema::hasColumn('tickets', 'restored_at')) {
                $table->timestamp('restored_at')->nullable()->after('restored_by');
            }

            if (! Schema::hasColumn('tickets', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('tickets', function (Blueprint $table) {
            if (! $this->indexExists('tickets', 'tickets_ticket_status_id_index')) {
                $table->index('ticket_status_id');
            }

            if (! $this->indexExists('tickets', 'tickets_ticket_priority_id_index')) {
                $table->index('ticket_priority_id');
            }

            if (! $this->indexExists('tickets', 'tickets_assigned_to_index')) {
                $table->index('assigned_to');
            }

            if (! $this->indexExists('tickets', 'tickets_due_date_index')) {
                $table->index('due_date');
            }

            if (! $this->indexExists('tickets', 'tickets_deleted_at_index')) {
                $table->index('deleted_at');
            }

            if (! $this->indexExists('tickets', 'tickets_restored_at_index')) {
                $table->index('restored_at');
            }

            if (! $this->indexExists('tickets', 'tickets_created_at_index')) {
                $table->index('created_at');
            }

            if (! $this->indexExists('tickets', 'tickets_updated_at_index')) {
                $table->index('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            $table->dropIndex(['updated_at']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['restored_at']);
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['ticket_priority_id']);
            $table->dropIndex(['ticket_status_id']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'restored_at', 'restored_by', 'deleted_by', 'updated_by',
                'created_by', 'meta', 'resolved_at', 'due_date',
                'assigned_to', 'ticket_priority_id', 'ticket_status_id',
                'description', 'title',
            ]);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(Schema::getIndexes($table))->pluck('name')->contains($indexName);
    }
};
