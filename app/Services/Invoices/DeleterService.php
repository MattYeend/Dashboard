<?php

namespace App\Services\Invoices;

use App\Actions\DeleteResource;
use App\Models\Invoice;
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
     * Soft delete a invoice.
     *
     * @throws \Exception
     */
    public function delete(
        Invoice $invoice,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $invoice,
            function (Invoice $invoice) use ($actor, $deletedBy): void {
                $invoice->deleted_by = $deletedBy;
                $invoice->deleted_at = now();
                $invoice->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_INVOICE,
                    $actor,
                    $invoice,
                    ['before' => $this->auditLogService->snapshot($invoice)],
                );
            });
    }

    /**
     * Force delete a invoice (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Invoice $invoice,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $invoice,
            function (Invoice $invoice) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_INVOICE,
                    $actor,
                    $invoice,
                    ['before' => $this->auditLogService->snapshot($invoice)],
                );
            });
    }

    /**
     * Delete multiple invoices.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $invoiceIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use (
            $invoiceIds,
            $deletedBy,
            &$count
        ) {
            $actor = User::findOrFail($deletedBy);
            $invoices = Invoice::whereIn('id', $invoiceIds)->get();

            foreach ($invoices as $invoice) {
                if ($this->delete(
                    $invoice,
                    $deletedBy,
                    $actor
                )) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
