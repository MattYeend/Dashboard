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
        Schema::table('categories', function (Blueprint $table) {
            $table->index('name');
            $table->index(['parent_id', 'name']);
            $table->index('deleted_at');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->index(['addressable_type', 'addressable_id', 'is_primary']);
            $table->index('postcode');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['parent_id', 'name']);
            $table->dropIndex(['deleted_at']);
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropIndex(['addressable_type', 'addressable_id', 'is_primary']);
            $table->dropIndex(['postcode']);
            $table->dropIndex(['deleted_at']);
        });
    }
};
