<?php

namespace App\Services\InvoiceStatuses;

use App\Actions\UpdateResource;
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
     * Update an existing task status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        InvoiceStatus $invoiceStatus,
        array $data,
        int $updatedBy
    ): InvoiceStatus {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($invoiceStatus);

        $invoiceStatusData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $invoiceStatus,
            $invoiceStatusData,
            function (InvoiceStatus $invoiceStatus) use ($actor, $before): void {
                $fresh = $invoiceStatus->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_INVOICE_STATUS,
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
