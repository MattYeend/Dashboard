<?php

namespace App\Services\Deals;

use App\Actions\DeleteResource;
use App\Models\Deal;
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
     * Soft delete a deal.
     *
     * @throws \Exception
     */
    public function delete(
        Deal $deal,
        int $deletedBy,
        ?User $actor = null
    ): bool {

        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $deal,
            function (Deal $deal) use ($actor, $deletedBy): void {
                $deal->deleted_by = $deletedBy;
                $deal->deleted_at = now();
                $deal->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_DEAL,
                    $actor,
                    $deal,
                    ['before' => $this->auditLogService->snapshot($deal)],
                );
            });
    }

    /**
     * Force delete a deal (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Deal $deal,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $deal,
            function (Deal $deal) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_DEAL,
                    $actor,
                    $deal,
                    ['before' => $this->auditLogService->snapshot($deal)],
                );
            });
    }

    /**
     * Delete multiple deals.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $dealIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($dealIds, $deletedBy, &$count) {
            $deals = Deal::whereIn('id', $dealIds)->get();

            foreach ($deals as $deal) {
                if ($this->delete($deal, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
