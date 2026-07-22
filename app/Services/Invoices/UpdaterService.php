<?php

namespace App\Services\Invoices;

use App\Actions\UpdateResource;
use App\Models\Invoice;
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

        $invoiceData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

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
}
