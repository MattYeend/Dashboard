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
        Schema::create('notification_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');

            // Who the broadcast goes to when sent: all users, a set of roles, or specific users.
            $table->string('audience_type')->default('all'); // all|role|users
            $table->json('audience_ids')->nullable(); // role names or user ids, depending on audience_type

            // Populated only once the broadcast has actually been dispatched.
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();

            $table->json('meta')->nullable();

            // Standard audit columns.
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Composite index for the common "unsent broadcasts of a given audience" lookup.
            $table->index(['audience_type', 'sent_at']);

            // Single-column indexes for sorting/filtering on the Index page.
            $table->index('title');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('restored_at');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_broadcasts');
    }
};
