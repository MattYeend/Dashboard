<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')
                ->nullable()
                ->constrained('organisations')
                ->cascadeOnDelete();

            // A payment can never outlive its invoice silently: deleting an
            // invoice that has payments is refused by the database.
            $table->foreignId('invoice_id')->constrained('invoices')->restrictOnDelete();
            $table->foreignId('status_id')->constrained('payment_statuses')->restrictOnDelete();
            $table->foreignId('parent_payment_id')->nullable()->constrained('payments')->restrictOnDelete();

            $table->string('type', 16)->default('payment');
            $table->string('method', 32);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('GBP');
            $table->string('reference', 128)->nullable();
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->timestamp('paid_at');
            $table->text('notes')->nullable();

            $table->json('meta')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('restored_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Stops an accidental double submit of the same payment. MySQL
            // allows many NULL references, so this only applies when one is
            // given. A trashed row keeps its reference until force deleted.
            $table->unique(['invoice_id', 'reference']);

            $table->index(['invoice_id', 'type', 'status_id']);
            $table->index('parent_payment_id');
            $table->index('paid_at');
            $table->index('created_by');
            $table->index('updated_by');
            $table->index('deleted_by');
            $table->index('restored_by');
            $table->index('restored_at');
            $table->index('deleted_at');
        });

        // Defence in depth for money: the database refuses a zero or
        // negative amount even if application code is bypassed.
        DB::statement('ALTER TABLE payments ADD CONSTRAINT payments_amount_positive CHECK (amount > 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
