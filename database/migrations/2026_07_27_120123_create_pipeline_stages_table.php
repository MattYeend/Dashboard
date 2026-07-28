<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipeline_stages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('pipeline_id')->constrained('pipelines')->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->string('background_colour')->default('#e5e7eb');
            $table->string('text_colour')->default('#111827');
            $table->boolean('is_won')->default(false);
            $table->boolean('is_lost')->default(false);
            $table->json('meta')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['pipeline_id', 'position'], 'pipeline_stages_pipeline_position_index');
            $table->index(['is_won', 'is_lost'], 'pipeline_stages_won_lost_index');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('restored_at');
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pipeline_stages');
    }
};
