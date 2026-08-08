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
        Schema::table('ticket_priorities', function (Blueprint $table) {
            if (! Schema::hasColumn('ticket_priorities', 'title')) {
                $table->string('title')->after('id');
            }

            if (! Schema::hasColumn('ticket_priorities', 'level')) {
                $table->unsignedTinyInteger('level')->default(1)->after('title');
            }

            if (! Schema::hasColumn('ticket_priorities', 'background_colour')) {
                $table->string('background_colour')->default('#6b7280')->after('level');
            }

            if (! Schema::hasColumn('ticket_priorities', 'text_colour')) {
                $table->string('text_colour')->default('#ffffff')->after('background_colour');
            }

            if (! Schema::hasColumn('ticket_priorities', 'meta')) {
                $table->json('meta')->nullable()->after('text_colour');
            }

            if (! Schema::hasColumn('ticket_priorities', 'created_by')) {
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('meta');
            }

            if (! Schema::hasColumn('ticket_priorities', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
            }

            if (! Schema::hasColumn('ticket_priorities', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete()->after('updated_by');
            }

            if (! Schema::hasColumn('ticket_priorities', 'restored_by')) {
                $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete()->after('deleted_by');
            }

            if (! Schema::hasColumn('ticket_priorities', 'restored_at')) {
                $table->timestamp('restored_at')->nullable()->after('restored_by');
            }

            if (! Schema::hasColumn('ticket_priorities', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('ticket_priorities', function (Blueprint $table) {
            if (! $this->indexExists('ticket_priorities', 'ticket_priorities_level_index')) {
                $table->index('level');
            }

            if (! $this->indexExists('ticket_priorities', 'ticket_priorities_deleted_at_index')) {
                $table->index('deleted_at');
            }

            if (! $this->indexExists('ticket_priorities', 'ticket_priorities_restored_at_index')) {
                $table->index('restored_at');
            }

            if (! $this->indexExists('ticket_priorities', 'ticket_priorities_created_at_index')) {
                $table->index('created_at');
            }

            if (! $this->indexExists('ticket_priorities', 'ticket_priorities_updated_at_index')) {
                $table->index('updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ticket_priorities', function (Blueprint $table) {
            $table->dropIndex(['updated_at']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['restored_at']);
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['level']);
            $table->dropSoftDeletes();
            $table->dropColumn([
                'restored_at', 'restored_by', 'deleted_by', 'updated_by',
                'created_by', 'meta', 'text_colour', 'background_colour',
                'level', 'title',
            ]);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return collect(Schema::getIndexes($table))->pluck('name')->contains($indexName);
    }
};
