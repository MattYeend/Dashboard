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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_name');
            $table->string('support_email');
            $table->string('timezone')->default('Europe/London');
            $table->string('date_format')->default('d/m/Y');
            $table->boolean('maintenance_mode')->default(false);
            $table->boolean('allow_registrations')->default(true);
            $table->unsignedSmallInteger('default_pagination')->default(15);
            $table->string('default_locale')->default('en_GB');
            $table->boolean('two_factor_required')->default(false);
            $table->unsignedSmallInteger('session_timeout_minutes')->default(120);
            $table->unsignedSmallInteger('max_login_attempts')->default(5);
            $table->unsignedSmallInteger('password_expiry_days')->nullable();
            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
