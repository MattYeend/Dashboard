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
        Schema::create('campaign_statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')
                ->nullable()
                ->constrained('organisations')
                ->cascadeOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('system_key', 64)->nullable();
            $table->string('background_colour', 7)->default('#e2e8f0');
            $table->string('text_colour', 7)->default('#1a202c');

            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organisation_id', 'created_at']);
            $table->index('system_key');
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
        Schema::dropIfExists('campaign_statuses');
    }
};
