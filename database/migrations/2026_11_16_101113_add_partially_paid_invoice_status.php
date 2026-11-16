<?php

declare(strict_types=1);

use App\Models\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::transaction(function (): void {
            $now = now();

            $organisationIds = DB::table('invoice_statuses')
                ->whereNotNull('organisation_id')
                ->distinct()
                ->pluck('organisation_id');

            foreach ($organisationIds as $organisationId) {
                // The query builder includes soft-deleted rows, so a trashed
                // "Partially Paid" status cannot trigger a duplicate key error.
                $exists = DB::table('invoice_statuses')
                    ->where('organisation_id', $organisationId)
                    ->where('title', 'Partially Paid')
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('invoice_statuses')->insert([
                    'organisation_id' => $organisationId,
                    'title' => 'Partially Paid',
                    'description' => 'Some, but not all, of the invoice has been paid.',
                    'background_colour' => '#bee3f8',
                    'text_colour' => '#2b6cb0',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        InvoiceStatus::withoutGlobalScopes()
            ->where('title', 'Partially Paid')
            ->whereDoesntHave('invoices')
            ->delete();
    }
};
