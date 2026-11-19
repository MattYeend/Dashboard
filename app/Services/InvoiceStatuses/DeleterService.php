<?php

declare(strict_types=1);

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
     * Soft delete an invoice status.
     *
     * @throws \DomainException When the status is a protected system status.
     * @throws \Exception
     */
    public function delete(
        InvoiceStatus $invoiceStatus,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $this->ensureNotSystem($invoiceStatus);

        $actor ??= User::findOrFail($deletedBy);

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
                    ['before' => $this->auditLogService->snapshot($invoiceStatus)],
                );
            });
    }

    /**
     * Force delete an invoice status (permanent deletion).
     *
     * @throws \DomainException When the status is a protected system status.
     * @throws \Exception
     */
    public function forceDelete(
        InvoiceStatus $invoiceStatus,
        int $deletedBy
    ): bool {
        $this->ensureNotSystem($invoiceStatus);

        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $invoiceStatus,
            function (InvoiceStatus $invoiceStatus) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_INVOICE_STATUS,
                    $actor,
                    $invoiceStatus,
                    ['before' => $this->auditLogService->snapshot($invoiceStatus)],
                );
            });
    }

    /**
     * Delete multiple invoice statuses.
     *
     * System statuses are skipped rather than rejected, so one protected row
     * cannot roll back the whole batch. The returned count excludes them.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $invoiceStatusIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($invoiceStatusIds, $deletedBy, &$count) {
            $invoiceStatuses = InvoiceStatus::whereIn('id', $invoiceStatusIds)->get();

            foreach ($invoiceStatuses as $invoiceStatus) {
                if ($invoiceStatus->isSystem()) {
                    continue;
                }

                if ($this->delete($invoiceStatus, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }

    /**
     * Refuse to delete a protected system status.
     *
     * @throws \DomainException
     */
    protected function ensureNotSystem(InvoiceStatus $invoiceStatus): void
    {
        if ($invoiceStatus->isSystem()) {
            throw new \DomainException('System statuses cannot be deleted.');
        }
    }
}
