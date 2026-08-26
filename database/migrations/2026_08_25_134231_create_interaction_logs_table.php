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
        Schema::create('interaction_logs', function (Blueprint $table) {
            $table->id();
            $table->morphs('interactable');
            $table->string('type');
            $table->string('subject');
            $table->text('outcome')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
            $table->foreignId('contact_id')->nullable()->constrained('contacts')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['interactable_type', 'interactable_id', 'occurred_at'], 'interaction_logs_interactable_occurred_index');
            $table->index('type');
            $table->index(['contact_id', 'occurred_at'], 'interaction_logs_contact_occurred_index');
            $table->index('occurred_at');
            $table->index('restored_at');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');
            $table->index(['deleted_at', 'type'], 'interaction_logs_deleted_type_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interaction_logs');
    }
};
