<?php

namespace App\Services\InvoiceStatuses;

use App\Actions\DeleteResource;
use App\Models\InvoiceStatus;
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
    ) {}

    /**
     * Soft delete a invoiceStatus.
     *
     * @throws \Exception
     */
    public function delete(
        InvoiceStatus $invoiceStatus,
        int $deletedBy,
        ?User $actor = null
    ): bool {

        $actor ??= User::findOrFail(
            $deletedBy
        );

        return $this->deleteResource->handle(
            $invoiceStatus,
            function (InvoiceStatus $invoiceStatus) use ($actor, $deletedBy): void {
                $invoiceStatus->deleted_by = $deletedBy;
                $invoiceStatus->deleted_at = now();
                $invoiceStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_INVOICE_STATUS,
                    $actor,
                    $invoiceStatus,
                    ['before' => $this->auditLogService->snapshot(
                        $invoiceStatus
                    ),
                    ],
                );
            });
    }

    /**
     * Force delete a invoiceStatus (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        InvoiceStatus $invoiceStatus,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail(
            $deletedBy
        );

        return $this->deleteResource->forceHandle(
            $invoiceStatus,
            function (InvoiceStatus $invoiceStatus) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_INVOICE_STATUS,
                    $actor,
                    $invoiceStatus,
                    ['before' => $this->auditLogService->snapshot(
                        $invoiceStatus
                    ),
                    ],
                );
            });
    }

    /**
     * Delete multiple invoiceStatuses.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $invoiceStatusIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use (
            $invoiceStatusIds,
            $deletedBy,
            &$count
        ) {
            $invoiceStatuses = InvoiceStatus::whereIn('id', $invoiceStatusIds)->get();

            foreach ($invoiceStatuses as $invoiceStatus) {
                if ($this->delete(
                    $invoiceStatus,
                    $deletedBy
                )) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
