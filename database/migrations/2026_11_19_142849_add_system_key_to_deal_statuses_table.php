<?php

declare(strict_types=1);

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
        Schema::table('deal_statuses', function (Blueprint $table): void {
            // Immutable key used by workflow code. Null for custom statuses.
            // A unique index over a nullable column allows many NULLs in MySQL.
            $table->string('system_key', 32)->nullable()->after('text_colour');

            // Unique per organisation, so each tenant gets its own system rows.
            $table->unique(['organisation_id', 'system_key'], 'deal_statuses_org_system_key_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deal_statuses', function (Blueprint $table): void {
            $table->dropUnique('deal_statuses_org_system_key_unique');
            $table->dropColumn('system_key');
        });
    }
};
