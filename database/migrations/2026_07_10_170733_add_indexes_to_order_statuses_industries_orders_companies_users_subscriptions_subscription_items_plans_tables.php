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
        Schema::table('order_statuses', function (Blueprint $table) {
            $table->index('title');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
            $table->index('restored_by');
            $table->index('restored_at');
            $table->index('deleted_at');
        });

        Schema::table('industries', function (Blueprint $table) {
            $table->index('title');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
            $table->index('restored_by');
            $table->index('restored_at');
            $table->index('deleted_at');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('due_at');
            $table->index('completed_at');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
            $table->index('restored_by');
            $table->index('restored_at');
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
            $table->index('restored_by');
            $table->index('restored_at');
            $table->index('deleted_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('pm_type');
            $table->index('trial_ends_at');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->index('stripe_price');
            $table->index('ends_at');
            $table->index('trial_ends_at');
        });

        Schema::table('subscription_items', function (Blueprint $table) {
            $table->index('meter_id');
            $table->index('meter_event_name');
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
            $table->index('restored_by');
            $table->index('restored_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_statuses', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['deleted_by']);
            $table->dropIndex(['restored_by']);
            $table->dropIndex(['restored_at']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('industries', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['deleted_by']);
            $table->dropIndex(['restored_by']);
            $table->dropIndex(['restored_at']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['due_at']);
            $table->dropIndex(['completed_at']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['deleted_by']);
            $table->dropIndex(['restored_by']);
            $table->dropIndex(['restored_at']);
        });

        Schema::table('companies', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['deleted_by']);
            $table->dropIndex(['restored_by']);
            $table->dropIndex(['restored_at']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['pm_type']);
            $table->dropIndex(['trial_ends_at']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropIndex(['stripe_price']);
            $table->dropIndex(['ends_at']);
            $table->dropIndex(['trial_ends_at']);
        });

        Schema::table('subscription_items', function (Blueprint $table) {
            $table->dropIndex(['meter_id']);
            $table->dropIndex(['meter_event_name']);
        });

        Schema::table('plans', function (Blueprint $table) {
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['deleted_by']);
            $table->dropIndex(['restored_by']);
            $table->dropIndex(['restored_at']);
        });
    }
};
