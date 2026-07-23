<?php

namespace App\Services\Invoices;

use App\Actions\RestoreResource;
use App\Models\Invoice;
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
    ) {}

    /**
     * Restore a soft-deleted invoice.
     *
     * @throws \Exception
     */
    public function restore(
        Invoice $invoice,
        int $restoredBy,
        ?User $actor = null
    ): Invoice {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $invoice,
            function (Invoice $invoice) use ($actor, $restoredBy): void {
                $invoice->restored_by = $restoredBy;
                $invoice->restored_at = now();
                $invoice->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_INVOICE,
                    $actor,
                    $invoice,
                    ['before' => $this->auditLogService->snapshot($invoice)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted invoices.
     *
     * @return int Number of invoices restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(array $invoiceIds, int $restoredBy): int
    {
        $count = 0;

        DB::transaction(function () use ($invoiceIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, Invoice> $invoices */
            $invoices = Invoice::withTrashed()
                ->whereIn('id', $invoiceIds)
                ->get();

            foreach ($invoices as $invoice) {
                if ($invoice->trashed()) {
                    $this->restore($invoice, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
