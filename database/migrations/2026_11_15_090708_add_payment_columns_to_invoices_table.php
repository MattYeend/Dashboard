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
            $table->unsignedBigInteger('amount_paid')->default(0)->after('total');
        });

        DB::statement('ALTER TABLE invoices ADD CONSTRAINT invoices_amount_paid_not_negative CHECK (amount_paid >= 0)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'amount_paid')) {
            if (DB::getDriverName() === 'pgsql') {
                DB::statement('ALTER TABLE invoices DROP CONSTRAINT IF EXISTS invoices_amount_paid_not_negative');
            }

            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('amount_paid');
            });
        }
    }
};
