<?php

namespace App\Services\InvoiceItems;

use App\Actions\DeleteResource;
use App\Actions\RecalculateInvoiceTotal;
use App\Models\InvoiceItem;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
        protected readonly RecalculateInvoiceTotal $recalculateInvoiceTotal,
    ) {}

    /**
     * Soft delete a invoiceItem.
     *
     * @throws \Exception
     */
    public function delete(
        InvoiceItem $invoiceItem,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail(
            $deletedBy
        );

        return $this->deleteResource->handle(
            $invoiceItem,
            function (InvoiceItem $invoiceItem) use ($actor, $deletedBy): void {
                $invoiceItem->deleted_by = $deletedBy;
                $invoiceItem->deleted_at = now();
                $invoiceItem->save();

                $this->recalculateInvoiceTotal->execute(
                    $invoiceItem->invoice
                );

                $this->auditLogService->record(
                    Log::ACTION_DELETE_INVOICE,
                    $actor,
                    $invoiceItem,
                    [
                        'before' => $this->auditLogService->snapshot(
                            $invoiceItem
                        ),
                    ],
                );
            }
        );
    }

    /**
     * Force delete a invoiceItem (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        InvoiceItem $invoiceItem,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail(
            $deletedBy
        );

        return $this->deleteResource->forceHandle(
            $invoiceItem,
            function (InvoiceItem $invoiceItem) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_INVOICE_ITEM,
                    $actor,
                    $invoiceItem,
                    [
                        'before' => $this->auditLogService->snapshot(
                            $invoiceItem
                        ),
                    ],
                );
            }
        );
    }

    /**
     * Delete multiple invoiceItems.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $invoiceItemIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use (
            $invoiceItemIds,
            $deletedBy,
            &$count
        ) {
            $actor = User::findOrFail(
                $deletedBy
            );
            $invoiceItems = InvoiceItem::whereIn('id', $invoiceItemIds)->get();

            foreach ($invoiceItems as $invoiceItem) {
                if ($this->delete(
                    $invoiceItem,
                    $deletedBy,
                    $actor
                )) {
                    $count++;
                }
            }
        }
        );

        return $count;
    }
}
