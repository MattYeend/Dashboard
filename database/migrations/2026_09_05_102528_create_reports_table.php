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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type');
            $table->string('format')->default('pdf');
            $table->json('filters')->nullable();

            $table->boolean('is_scheduled')->default(false);
            $table->string('schedule_frequency')->nullable();
            $table->time('schedule_time')->nullable();
            $table->json('recipients')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamp('next_run_at')->nullable();

            $table->json('meta')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('title');
            $table->index('type');
            $table->index('format');
            $table->index('is_scheduled');
            $table->index('next_run_at');
            $table->index(['is_scheduled', 'next_run_at']);
            $table->index('created_at');
            $table->index('updated_at');
            $table->index('deleted_at');
            $table->index('restored_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
