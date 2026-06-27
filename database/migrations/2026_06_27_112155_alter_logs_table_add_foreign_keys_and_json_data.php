<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('UPDATE logs SET data = NULL WHERE data IS NOT NULL AND JSON_VALID(data) = 0');

        Schema::table('logs', function (Blueprint $table) {
            $table->unsignedBigInteger('action_id')
                  ->change();

            $table->json('data')
                  ->nullable()
                  ->change();

            $table->foreign('logged_in_user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            $table->foreign('related_to_user_id')
                  ->references('id')
                  ->on('users')
                  ->nullOnDelete();

            $table->index('action_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropForeign(['logged_in_user_id']);
            $table->dropForeign(['related_to_user_id']);
            $table->dropIndex(['action_id']);

            $table->bigInteger('action_id')->change();
            $table->text('data')->nullable()->change();
        });
    }
};
