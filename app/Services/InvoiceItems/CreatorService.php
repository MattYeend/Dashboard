<?php

namespace App\Services\InvoiceItems;

use App\Actions\CreateResource;
use App\Actions\Invoice\RecalculateInvoiceTotal;
use App\Models\InvoiceItem;
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
        protected readonly RecalculateInvoiceTotal $recalculateInvoiceTotal,
    ) {}

    /**
     * Create a new invoice item.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(
        array $data,
        int $invoiceId,
        int $createdBy
    ): InvoiceItem {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($invoiceId, $createdBy, $actor): InvoiceItem {
                $invoiceItemData = $this->dataPreparation->prepareForCreation(
                    $data,
                    $invoiceId,
                    $createdBy
                );

                $newInvoiceItem = InvoiceItem::create(
                    $invoiceItemData
                );

                $this->recalculateInvoiceTotal->execute(
                    $newInvoiceItem->invoice
                );

                $this->auditLogService->record(
                    Log::ACTION_CREATE_INVOICE_ITEM,
                    $actor,
                    $newInvoiceItem,
                    [
                        'after' => $this->auditLogService->snapshot(
                            $newInvoiceItem
                        ),
                    ],
                );

                return $newInvoiceItem;
            }
        );
    }
}
