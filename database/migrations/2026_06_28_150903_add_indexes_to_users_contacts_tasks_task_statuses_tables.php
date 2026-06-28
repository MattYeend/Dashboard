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
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
            $table->index('restored_by');
            $table->index('restored_at');
            $table->index('deleted_at');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->index('email');
            $table->index('country');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
            $table->index('restored_by');
            $table->index('restored_at');
            $table->index('deleted_at');
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->index('assigned_to');
            $table->index('status_id');
            $table->index('due_date');
            $table->index('assigned_date');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
            $table->index('restored_by');
            $table->index('restored_at');
            $table->index('deleted_at');
        });

        Schema::table('task_statuses', function (Blueprint $table) {
            $table->index('title');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
            $table->index('restored_by');
            $table->index('restored_at');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['deleted_by']);
            $table->dropIndex(['restored_by']);
            $table->dropIndex(['restored_at']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropIndex(['email']);
            $table->dropIndex(['country']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['deleted_by']);
            $table->dropIndex(['restored_by']);
            $table->dropIndex(['restored_at']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['assigned_to']);
            $table->dropIndex(['status_id']);
            $table->dropIndex(['due_date']);
            $table->dropIndex(['assigned_date']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['deleted_by']);
            $table->dropIndex(['restored_by']);
            $table->dropIndex(['restored_at']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('task_statuses', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['deleted_by']);
            $table->dropIndex(['restored_by']);
            $table->dropIndex(['restored_at']);
            $table->dropIndex(['deleted_at']);
        });
    }
};
