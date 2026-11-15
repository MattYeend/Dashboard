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
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('amount_paid', 10, 2)->default(0.00)->after('total_amount');
        });

        DB::statement('ALTER TABLE invoices ADD CONSTRAINT invoices_amount_paid_not_negative CHECK (amount_paid >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE invoices DROP CONSTRAINT invoices_amount_paid_not_negative');

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['amount_paid', 'paid_at']);
        });
    }
};
