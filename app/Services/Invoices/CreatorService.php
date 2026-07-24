<?php

namespace App\Services\Invoices;

use App\Actions\CreateResource;
use App\Models\Invoice;
use App\Models\Log;
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
     * Create a new invoice.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(
        array $data,
        int $createdBy
    ): Invoice {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Invoice {
                $invoiceData = $this->dataPreparation->prepareForCreation(
                    $data,
                    $createdBy
                );

                $newInvoice = Invoice::create(
                    $invoiceData
                );

                $contactData = $this->dataPreparation->prepareContactForCreation(
                    $data
                );

                if ($contactData !== null) {
                    $newInvoice->contact()->create($contactData);
                }

                $this->auditLogService->record(
                    Log::ACTION_CREATE_INVOICE,
                    $actor,
                    $newInvoice,
                    [
                        'after' => $this->auditLogService->snapshot(
                            $newInvoice
                        ),
                    ],
                );

                return $newInvoice;
            }
        );
    }
}
