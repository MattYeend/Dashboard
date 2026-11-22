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
        Schema::create('campaign_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained();

            $table->string('name');
            $table->text('description')->nullable();
            $table->json('criteria')->nullable();
            $table->unsignedInteger('estimated_recipients_count')->default(0);
            $table->timestamp('last_refreshed_at')->nullable();

            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organisation_id', 'created_at']);
            $table->index(['organisation_id', 'name']);
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_segments');
    }
};