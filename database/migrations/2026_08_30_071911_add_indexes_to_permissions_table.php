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
        Schema::table('permissions', function (Blueprint $table) {
            $table->index(['name', 'guard_name']);
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');
            $table->index('restored_at');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
            $table->index('restored_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropIndex(['name', 'guard_name']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
            $table->dropIndex(['deleted_at']);
            $table->dropIndex(['restored_at']);
            $table->dropIndex(['created_by']);
            $table->dropIndex(['updated_by']);
            $table->dropIndex(['deleted_by']);
            $table->dropIndex(['restored_by']);
        });
    }
};
