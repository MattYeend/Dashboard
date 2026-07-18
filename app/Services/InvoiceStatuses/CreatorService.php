<?php

namespace App\Services\InvoiceStatuses;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\InvoiceStatus;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Create a new invoiceStatus.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): InvoiceStatus
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): InvoiceStatus {
                $invoiceStatusData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newInvoiceStatus = InvoiceStatus::create($invoiceStatusData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_INVOICE_STATUS,
                    $actor,
                    $newInvoiceStatus,
                    ['after' => $this->auditLogService->snapshot($newInvoiceStatus)],
                );

                return $newInvoiceStatus;
            });
    }
}
