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
            $table->json('meta')->nullable()->after('guard_name');
            $table->foreignId('created_by')->nullable()->after('meta')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->after('updated_by')->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->after('deleted_by')->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable()->after('restored_by');
            $table->softDeletes()->after('restored_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropConstrainedForeignId('deleted_by');
            $table->dropConstrainedForeignId('restored_by');
            $table->dropColumn(['meta', 'restored_at', 'deleted_at']);
        });
    }
};
