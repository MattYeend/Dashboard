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
            $table->unsignedBigInteger('sequence')->nullable()->unique();
            $table->char('previous_hash', 64)->nullable();
            $table->char('hash', 64)->nullable()->unique();
            $table->timestamp('sealed_at')->nullable()->index();

            $table->index(['action_id', 'created_at']);
            $table->index(['logged_in_user_id', 'created_at']);
            $table->index('related_to_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table): void {
            $table->dropIndex(['action_id', 'created_at']);
            $table->dropIndex(['logged_in_user_id', 'created_at']);
            $table->dropIndex(['related_to_user_id']);
            $table->dropColumn(['sequence', 'previous_hash', 'hash', 'sealed_at']);
        });
    }
};
