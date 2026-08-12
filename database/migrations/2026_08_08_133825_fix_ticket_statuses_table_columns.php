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
        Schema::table('ticket_statuses', function (Blueprint $table) {
            if (! Schema::hasColumn('ticket_statuses', 'title')) {
                $table->string('title')->unique()->after('id');
            }

            if (! Schema::hasColumn('ticket_statuses', 'description')) {
                $table->string('description')->nullable()->after('title');
            }

            if (! Schema::hasColumn('ticket_statuses', 'background_colour')) {
                $table->string('background_colour')->default('#6b7280')->after('description');
            }

            if (! Schema::hasColumn('ticket_statuses', 'text_colour')) {
                $table->string('text_colour')->default('#ffffff')->after('background_colour');
            }

            if (! Schema::hasColumn('ticket_statuses', 'meta')) {
                $table->json('meta')->nullable()->after('text_colour');
            }

            if (! Schema::hasColumn('ticket_statuses', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('meta');
            }

            if (! Schema::hasColumn('ticket_statuses', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            }

            if (! Schema::hasColumn('ticket_statuses', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete()->after('updated_by');
            }

            if (! Schema::hasColumn('ticket_statuses', 'restored_by')) {
                $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete()->after('deleted_by');
            }

            if (! Schema::hasColumn('ticket_statuses', 'restored_at')) {
                $table->timestamp('restored_at')->nullable()->after('restored_by');
            }

            if (! Schema::hasColumn('ticket_statuses', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('ticket_statuses', function (Blueprint $table) {
            if (! $this->indexExists('ticket_statuses', 'ticket_statuses_restored_at_index')) {
                $table->index('restored_at');
            }

            if (! $this->indexExists('ticket_statuses', 'ticket_statuses_deleted_at_index')) {
                $table->index('deleted_at');
            }

            if (! $this->indexExists('ticket_statuses', 'ticket_statuses_deleted_at_title_index')) {
                $table->index(['deleted_at', 'title']);
            }

            if (! $this->indexExists('ticket_statuses', 'ticket_statuses_created_at_index')) {
                $table->index('created_at');
            }

            if (! $this->indexExists('ticket_statuses', 'ticket_statuses_updated_at_index')) {
                $table->index('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_statuses', function (Blueprint $table) {
            $table->dropIndex(['updated_at']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['deleted_at', 'title']);
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['restored_at']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'restored_at', 'restored_by', 'deleted_by', 'updated_by',
                'created_by', 'meta', 'text_colour', 'background_colour',
                'description', 'title',
            ]);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(Schema::getIndexes($table))->pluck('name')->contains($indexName);
    }
};
