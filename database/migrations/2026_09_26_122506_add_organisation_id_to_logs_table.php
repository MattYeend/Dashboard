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
        Schema::table('logs', function (Blueprint $table): void {
            $table->foreignId('organisation_id')
                ->nullable()
                ->after('id')
                ->constrained('organisations')
                ->nullOnDelete();

            $table->index(['organisation_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('organisation_id');
        });
    }
};
