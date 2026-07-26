<?php

namespace App\Services\InvoiceItems;

use App\Actions\RecalculateInvoiceTotal;
use App\Actions\RestoreResource;
use App\Models\InvoiceItem;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
        protected readonly RecalculateInvoiceTotal $recalculateInvoiceTotal,
    ) {}

    /**
     * Restore a soft-deleted invoice item.
     *
     * @throws \Exception
     */
    public function restore(
        InvoiceItem $invoiceItem,
        int $restoredBy,
        ?User $actor = null
    ): InvoiceItem {
        $actor ??= User::findOrFail(
            $restoredBy
        );

        return $this->restoreResource->handle(
            $invoiceItem,
            function (InvoiceItem $invoiceItem) use ($actor, $restoredBy): void {
                $invoiceItem->restored_by = $restoredBy;
                $invoiceItem->restored_at = now();
                $invoiceItem->save();

                $this->recalculateInvoiceTotal->execute(
                    $invoiceItem->invoice
                );

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_INVOICE_ITEM,
                    $actor,
                    $invoiceItem,
                    ['before' => $this->auditLogService->snapshot($invoiceItem)],
                );
            }
        );
    }

    /**
     * Restore multiple soft-deleted invoice items.
     *
     * @return int Number of invoice items restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $invoiceItemIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use (
            $invoiceItemIds,
            $restoredBy,
            &$count
        ) {
            $actor = User::findOrFail(
                $restoredBy
            );

            /** @var Collection<int, InvoiceItem> $invoiceItems */
            $invoiceItems = InvoiceItem::withTrashed()
                ->whereIn('id', $invoiceItemIds)
                ->get();

            foreach ($invoiceItems as $invoiceItem) {
                if ($invoiceItem->trashed()) {
                    $this->restore(
                        $invoiceItem,
                        $restoredBy,
                        $actor
                    );
                    $count++;
                }
            }
        });

        return $count;
    }
}
