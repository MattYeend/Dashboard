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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained();
            $table->foreignId('course_id')->constrained();
            $table->string('title');
            $table->string('slug');
            $table->string('summary', 500)->nullable();
            $table->longText('content')->nullable();
            $table->string('video_url', 2048)->nullable();
            $table->unsignedInteger('position')->default(1);
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->boolean('is_preview')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        
            $table->unique(['course_id', 'slug']);
            $table->index('published_at');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');
            $table->index('restored_at');
            $table->index(['organisation_id', 'published_at']);
            $table->index(['organisation_id', 'title']);
            $table->index(['course_id', 'position']);
            $table->index(['course_id', 'published_at']);
            $table->index(['course_id', 'is_preview']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
