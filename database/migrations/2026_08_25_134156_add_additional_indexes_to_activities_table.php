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
        Schema::table('activities', function (Blueprint $table) {
            $table->index('restored_at');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index(['deleted_at', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['activities_restored_at_index']);
            $table->dropIndex(['activities_created_at_index']);
            $table->dropIndex(['activities_updated_at_index']);
            $table->dropIndex(['activities_deleted_at_type_index']);
        });
    }
};
