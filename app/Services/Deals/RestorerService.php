<?php

namespace App\Services\Deals;

use App\Actions\RestoreResource;
use App\Models\Deal;
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
     * Restore a soft-deleted deal.
     *
     * @throws \Exception
     */
    public function restore(
        Deal $deal,
        int $restoredBy,
        ?User $actor = null,
    ): Deal {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $deal,
            function (Deal $deal) use ($actor, $restoredBy): void {
                $deal->restored_by = $restoredBy;
                $deal->restored_at = now();
                $deal->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_DEAL,
                    $actor,
                    $deal,
                    ['before' => $this->auditLogService->snapshot($deal)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted deals.
     *
     * @return int Number of deals restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $dealIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($dealIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,Deal> $deals */
            $deals = Deal::withTrashed()
                ->whereIn('id', $dealIds)
                ->get();

            foreach ($deals as $deal) {
                if ($deal->trashed()) {
                    $this->restore($deal, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
