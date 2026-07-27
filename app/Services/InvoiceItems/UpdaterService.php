<?php

namespace App\Services\InvoiceItems;

use App\Actions\Invoice\RecalculateInvoiceTotal;
use App\Actions\UpdateResource;
use App\Models\InvoiceItem;
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
        protected readonly RecalculateInvoiceTotal $recalculateInvoiceTotal,
    ) {}

    /**
     * Update an existing invoice item.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        InvoiceItem $invoiceItem,
        array $data,
        int $updatedBy
    ): InvoiceItem {
        $actor = User::findOrFail(
            $updatedBy
        );

        $before = $this->auditLogService->snapshot(
            $invoiceItem
        );

        $data['current_quantity'] = $invoiceItem->quantity;
        $data['current_unit_price'] = $invoiceItem->unit_price;
        $data['current_tax_rate'] = $invoiceItem->tax_rate;

        $invoiceItemData = $this->dataPreparation->prepareForUpdate(
            $data,
            $updatedBy
        );

        return $this->updateResource->handle(
            $invoiceItem,
            $invoiceItemData,
            function (InvoiceItem $invoiceItem) use ($actor, $before): void {
                $fresh = $invoiceItem->fresh();

                $this->recalculateInvoiceTotal->execute(
                    $fresh->invoice
                );

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_INVOICE_ITEM,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot(
                            $fresh
                        ),
                    ],
                );
            }
        );
    }
}
