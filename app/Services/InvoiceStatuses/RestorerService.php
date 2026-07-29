<?php

namespace App\Services\InvoiceStatuses;

use App\Actions\RestoreResource;
use App\Models\InvoiceStatus;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the resorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted invoice status.
     *
     * @throws \Exception
     */
    public function restore(
        InvoiceStatus $invoiceStatus,
        int $restoredBy,
        ?User $actor = null,
    ): InvoiceStatus {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $invoiceStatus,
            function (InvoiceStatus $invoiceStatus) use ($actor, $restoredBy): void {
                $invoiceStatus->restored_by = $restoredBy;
                $invoiceStatus->restored_at = now();
                $invoiceStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_INVOICE_STATUS,
                    $actor,
                    $invoiceStatus,
                    ['before' => $this->auditLogService->snapshot($invoiceStatus)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted invoice statuses.
     *
     * @return int Number of invoice statuses restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $invoiceStatusIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($invoiceStatusIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,InvoiceStatus> $invoiceStatuses */
            $invoiceStatuses = InvoiceStatus::withTrashed()
                ->whereIn('id', $invoiceStatusIds)
                ->get();

            foreach ($invoiceStatuses as $invoiceStatus) {
                if ($invoiceStatus->trashed()) {
                    $this->restore($invoiceStatus, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
