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
        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->text('answer');
            $table->boolean('is_correct')->default(false);
            $table->text('feedback')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_correct');
            $table->index('position');
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');
            $table->index('restored_at');
            $table->index(['organisation_id', 'created_at']);
            $table->index(['organisation_id', 'question_id'], 'answers_org_question_index');
            $table->index(['question_id', 'position'], 'answers_question_position_index');
            $table->index(['question_id', 'is_correct'], 'answers_question_correct_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answers');
    }
};
