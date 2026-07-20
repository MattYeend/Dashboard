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
        Schema::table('invoice_statuses', function (Blueprint $table) {
            $table->index('title');
            $table->index(['deleted_at', 'created_at']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->index(['post_id', 'created_at']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_statuses', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['deleted_at', 'created_at']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['post_id', 'created_at']);
            $table->dropIndex(['deleted_at']);
        });
    }
};
