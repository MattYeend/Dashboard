<?php

declare(strict_types=1);

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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')
                ->nullable()
                ->constrained('organisations')
                ->cascadeOnDelete();

            $table->string('name');
            $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->foreignId('campaign_segment_id')->nullable()->constrained('campaign_segments')->nullOnDelete();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('subject')->nullable();
            $table->string('from_name', 120)->nullable();
            $table->string('reply_to')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('rendered_subject')->nullable();
            $table->longText('rendered_body_html')->nullable();
            $table->longText('rendered_body_text')->nullable();
            $table->unsignedInteger('recipients_count')->nullable()->default(0);
            $table->unsignedInteger('sent_count')->nullable()->default(0);
            $table->unsignedInteger('failed_count')->nullable()->default(0);
            $table->unsignedInteger('skipped_count')->nullable()->default(0);
            $table->unsignedInteger('opened_count')->nullable()->default(0);
            $table->unsignedInteger('unsubscribed_count')->nullable()->default(0);
            $table->foreignId('status_id')->nullable()->constrained('campaign_statuses')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organisation_id', 'created_at']);
            $table->index('email_template_id');
            $table->index('campaign_segment_id');
            $table->index('owner_id');
            $table->index('status_id');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
            $table->index('restored_by');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};