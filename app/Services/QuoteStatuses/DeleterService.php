<?php

declare(strict_types=1);

namespace App\Services\QuoteStatuses;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\QuoteStatus;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete a quote status.
     *
     * @throws AuthorizationException
     * @throws \Exception
     */
    public function delete(
        QuoteStatus $quoteStatus,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        if (! $this->activeChecker->canBeDeleted($quoteStatus)) {
            throw new AuthorizationException('System statuses cannot be deleted.');
        }

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $quoteStatus,
            function (QuoteStatus $quoteStatus) use ($actor, $deletedBy): void {
                $quoteStatus->deleted_by = $deletedBy;
                $quoteStatus->deleted_at = now();
                $quoteStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_QUOTE_STATUS,
                    $actor,
                    $quoteStatus,
                    ['before' => $this->auditLogService->snapshot($quoteStatus)],
                );
            });
    }

    /**
     * Force delete a quote status (permanent deletion).
     *
     * Only a trashed status can reach this, and a system status can never
     * be trashed, so no system guard is needed here.
     *
     * @throws \Exception
     */
    public function forceDelete(
        QuoteStatus $quoteStatus,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $quoteStatus,
            function (QuoteStatus $quoteStatus) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_QUOTE_STATUS,
                    $actor,
                    $quoteStatus,
                    ['before' => $this->auditLogService->snapshot($quoteStatus)],
                );
            });
    }

    /**
     * Delete multiple quote statuses.
     *
     * Each status is routed through delete(), so the system status guard
     * applies to every item.
     *
     * @param  array<int, int>  $quoteStatusIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $quoteStatusIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($quoteStatusIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $quoteStatuses = QuoteStatus::whereIn('id', $quoteStatusIds)->get();

            foreach ($quoteStatuses as $quoteStatus) {
                if ($this->delete($quoteStatus, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
