<?php

declare(strict_types=1);

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
     * Update an existing invoice status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \DomainException When a system status would be renamed.
     * @throws \Exception
     */
    public function update(
        InvoiceStatus $invoiceStatus,
        array $data,
        int $updatedBy
    ): InvoiceStatus {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($invoiceStatus);

        $invoiceStatusData = $this->dataPreparation->prepareForUpdate($data);

        $this->ensureNotRenamed($invoiceStatus, $invoiceStatusData);

        return $this->updateResource->handle(
            $invoiceStatus,
            $invoiceStatusData,
            function (InvoiceStatus $invoiceStatus) use ($actor, $before, $updatedBy): void {
                $invoiceStatus->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
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

    /**
     * Refuse to rename a protected system status.
     *
     * The title is part of the workflow contract for system statuses. An
     * unchanged title is allowed so edit forms that always send it still work.
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws \DomainException
     */
    protected function ensureNotRenamed(InvoiceStatus $invoiceStatus, array $payload): void
    {
        if (
            $invoiceStatus->isSystem()
            && array_key_exists('title', $payload)
            && $payload['title'] !== $invoiceStatus->title
        ) {
            throw new \DomainException('System statuses cannot be renamed.');
        }
    }
}
