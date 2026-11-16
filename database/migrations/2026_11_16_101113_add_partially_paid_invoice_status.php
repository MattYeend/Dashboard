<?php

declare(strict_types=1);

use App\Models\InvoiceStatus;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        InvoiceStatus::query()
            ->withoutGlobalScopes()
            ->select('organisation_id')
            ->distinct()
            ->pluck('organisation_id')
            ->each(function (int $organisationId): void {
                InvoiceStatus::withoutGlobalScopes()->updateOrCreate(
                    [
                        'title' => 'Partially Paid',
                        'organisation_id' => $organisationId,
                    ],
                    [
                        'description' => 'Some, but not all, of the invoice has been paid.',
                        'background_colour' => '#bee3f8',
                        'text_colour' => '#2b6cb0',
                    ],
                );
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
