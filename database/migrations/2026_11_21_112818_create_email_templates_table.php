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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')
                ->nullable()
                ->constrained('organisations')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('subject');
            $table->string('preheader')->nullable();
            $table->longText('body_html');
            $table->text('body_text')->nullable();
            $table->boolean('is_active')->default(true);

            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['organisation_id', 'created_at']);
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
        Schema::dropIfExists('email_templates');
    }
};