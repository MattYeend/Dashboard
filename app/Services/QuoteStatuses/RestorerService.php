<?php

declare(strict_types=1);

namespace App\Services\QuoteStatuses;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\QuoteStatus;
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
     * Restore a soft-deleted quote status.
     *
     * @throws \Exception
     */
    public function restore(
        QuoteStatus $quoteStatus,
        int $restoredBy,
        ?User $actor = null
    ): QuoteStatus {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $quoteStatus,
            function (QuoteStatus $quoteStatus) use ($actor, $restoredBy): void {
                $quoteStatus->restored_by = $restoredBy;
                $quoteStatus->restored_at = now();
                $quoteStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_QUOTE_STATUS,
                    $actor,
                    $quoteStatus,
                    ['before' => $this->auditLogService->snapshot($quoteStatus)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted quote statuses.
     *
     * @param  array<int, int>  $quoteStatusIds
     * @return int Number of quote statuses restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $quoteStatusIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($quoteStatusIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,QuoteStatus> $quoteStatuses */
            $quoteStatuses = QuoteStatus::withTrashed()
                ->whereIn('id', $quoteStatusIds)
                ->get();

            foreach ($quoteStatuses as $quoteStatus) {
                if ($quoteStatus->trashed()) {
                    $this->restore($quoteStatus, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
