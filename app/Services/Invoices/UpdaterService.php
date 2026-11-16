<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use App\Actions\Invoice\MarkInvoiceAsPaid;
use App\Actions\Invoice\SendInvoiceToCustomer;
use App\Actions\UpdateResource;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Log;
use App\Models\User;
use App\Notifications\InvoiceOverdueNotification;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
        protected readonly MarkInvoiceAsPaid $markInvoiceAsPaid,
        protected readonly SendInvoiceToCustomer $sendInvoiceToCustomer,
        protected readonly BalanceService $balance,
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
        $invoiceData = $this->dataPreparation->prepareForUpdate($data);

        return DB::transaction(function () use ($invoice, $data, $invoiceData, $actor, $updatedBy): Invoice {
            $invoice = Invoice::query()->whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            $hasTotal = array_key_exists('total', $invoiceData);

            if ($hasTotal && (int) $invoiceData['total'] < (int) $invoice->amount_paid) {
                throw ValidationException::withMessages([
                    'total' => 'The total cannot be lower than the amount already paid.',
                ]);
            }

            $totalChanged = $hasTotal && (int) $invoiceData['total'] !== (int) $invoice->total;
            $before = $this->auditLogService->snapshot($invoice);

            return $this->updateResource->handle(
                $invoice,
                $invoiceData,
                function (Invoice $invoice) use ($actor, $before, $data, $updatedBy, $totalChanged): void {
                    $invoice->forceFill([
                        'updated_by' => $updatedBy,
                    ])->save();

                    $contactData = $this->dataPreparation->prepareUpdate($data);

                    if ($contactData !== null) {
                        $contact = $invoice->contact()->updateOrCreate([], $contactData);

                        $addressData = $this->dataPreparation->prepareAddressForContactUpdate($data);

                        if ($addressData !== null) {
                            $contact->addresses()->updateOrCreate([], $addressData);
                        }
                    }

                    if ($totalChanged) {
                        $this->balance->recalculate($invoice, $actor);
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
        });
    }

    /**
     * Mark an invoice as sent.
     *
     * Sets status_id to the 'Sent' status, records sent_at, and emails
     * the invoice PDF to the customer.
     */
    public function markAsSent(Invoice $invoice, int $actorId): Invoice
    {
        $actor = User::findOrFail($actorId);
        $before = $this->auditLogService->snapshot($invoice);

        $status = InvoiceStatus::where('title', 'Sent')->first();

        $invoice->update([
            'status_id' => $status?->id,
            'sent_at' => now(),
        ]);

        $invoice->forceFill([
            'updated_by' => $actorId,
        ])->save();

        $fresh = $invoice->fresh();

        $this->sendInvoiceToCustomer->execute($fresh);

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

        $fresh = $this->markInvoiceAsPaid->execute($invoice, $actorId);

        $this->auditLogService->record(
            Log::ACTION_MARK_INVOICE_PAID,
            $actor,
            $fresh,
            [
                'before' => $before,
                'after' => $this->auditLogService->snapshot($fresh),
            ],
        );

        return $fresh;
    }

    /**
     * Mark a paid invoice as unpaid again.
     *
     * Reverts status_id to 'Pending' and clears paid_at.
     */
    public function markAsUnpaid(Invoice $invoice, int $actorId): Invoice
    {
        if ($invoice->payments()->exists()) {
            throw ValidationException::withMessages([
                'invoice' => 'An invoice with payments cannot be marked as unpaid. Remove the payments instead.',
            ]);
        }
        
        $actor = User::findOrFail($actorId);
        $before = $this->auditLogService->snapshot($invoice);

        $status = InvoiceStatus::where('title', 'Pending')->first();

        $invoice->update([
            'status_id' => $status?->id,
            'paid_at' => null,
        ]);

        $invoice->forceFill([
            'updated_by' => $actorId,
        ])->save();

        $fresh = $invoice->fresh();

        $this->auditLogService->record(
            Log::ACTION_MARK_INVOICE_UNPAID,
            $actor,
            $fresh,
            [
                'before' => $before,
                'after' => $this->auditLogService->snapshot($fresh),
            ],
        );

        return $fresh;
    }

    /**
     * Notify the invoice's account manager that the invoice is overdue,
     * and stamp it so it isn't notified again.
     */
    public function markOverdueNotified(Invoice $invoice): Invoice
    {
        $invoice->company?->accountManager?->notify(new InvoiceOverdueNotification($invoice));

        $invoice->update([
            'overdue_notified_at' => now(),
        ]);

        return $invoice->fresh();
    }
}
