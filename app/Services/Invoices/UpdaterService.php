<?php

namespace App\Services\Invoices;

use App\Actions\UpdateResource;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing invoice.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Invoice $invoice,
        array $data,
        int $updatedBy
    ): Invoice {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($invoice);

        $invoiceData = $this->dataPreparation->prepareForUpdate(
            $data,
            $updatedBy
        );

        return $this->updateResource->handle(
            $invoice,
            $invoiceData,
            function (Invoice $invoice) use ($actor, $before, $data): void {
                $contactData = $this->dataPreparation->prepareContactForUpdate($data);

                if ($contactData !== null) {
                    $invoice->contact()->updateOrCreate([], $contactData);
                }

                $fresh = $invoice->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_INVOICE,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }

    /**
     * Mark an invoice as sent.
     *
     * Sets status_id to the 'Sent' status and records sent_at.
     *
     * TODO: dispatch the invoice email/PDF here once invoice items
     * are complete - this currently only updates status and timestamp.
     */
    public function markAsSent(
        Invoice $invoice,
        int $actorId
    ): Invoice {
        $actor = User::findOrFail($actorId);
        $before = $this->auditLogService->snapshot($invoice);

        $status = InvoiceStatus::where('title', 'Sent')->first();

        $invoice->update([
            'status_id' => $status?->id,
            'sent_at' => now(),
            'updated_by' => $actorId,
        ]);

        $fresh = $invoice->fresh();

        $this->auditLogService->record(
            Log::ACTION_SEND_INVOICE,
            $actor,
            $fresh,
            ['before' => $before, 'after' => $this->auditLogService->snapshot($fresh)],
        );

        return $fresh;
    }

    /**
     * Mark an invoice as paid.
     *
     * Sets status_id to the 'Paid' status and records paid_at.
     */
    public function markAsPaid(Invoice $invoice, int $actorId): Invoice
    {
        $actor = User::findOrFail($actorId);
        $before = $this->auditLogService->snapshot($invoice);

        $status = InvoiceStatus::where('title', 'Paid')->first();

        $invoice->update([
            'status_id' => $status?->id,
            'paid_at' => now(),
            'updated_by' => $actorId,
        ]);

        $fresh = $invoice->fresh();

        $this->auditLogService->record(
            Log::ACTION_MARK_INVOICE_PAID,
            $actor,
            $fresh,
            ['before' => $before, 'after' => $this->auditLogService->snapshot($fresh)],
        );

        return $fresh;
    }

    /**
     * Mark a paid invoice as unpaid again.
     *
     * Reverts status_id to 'Pending' and clears paid_at.
     */
    public function markAsUnpaid(
        Invoice $invoice,
        int $actorId
    ): Invoice {
        $actor = User::findOrFail($actorId);
        $before = $this->auditLogService->snapshot($invoice);

        $status = InvoiceStatus::where('title', 'Pending')->first();

        $invoice->update([
            'status_id' => $status?->id,
            'paid_at' => null,
            'updated_by' => $actorId,
        ]);

        $fresh = $invoice->fresh();

        $this->auditLogService->record(
            Log::ACTION_MARK_INVOICE_UNPAID,
            $actor,
            $fresh,
            ['before' => $before, 'after' => $this->auditLogService->snapshot($fresh)],
        );

        return $fresh;
    }
}
