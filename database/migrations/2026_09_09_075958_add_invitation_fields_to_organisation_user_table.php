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
        Schema::table('organisation_user', function (Blueprint $table) {
            $table->string('status')->default('invited')->after('user_id');
            $table->string('invitation_token')->nullable()->unique()->after('status');
            $table->timestamp('invited_at')->nullable()->after('invitation_token');
            $table->timestamp('joined_at')->nullable()->after('invited_at');
            $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete()->after('joined_at');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->after('invited_by');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->after('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisation_user', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invited_by');
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('updated_by');
            $table->dropColumn(['status', 'invitation_token', 'invited_at', 'joined_at']);
        });
    }
};
