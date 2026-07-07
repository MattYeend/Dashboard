<?php

namespace App\Services\OrderStatuses;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\OrderStatus;
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
     * Restore a soft-deleted orderStatus.
     *
     * @throws \Exception
     */
    public function restore(
        OrderStatus $orderStatus,
        int $restoredBy,
        ?User $actor = null
    ): OrderStatus {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $orderStatus,
            function (OrderStatus $orderStatus) use ($actor, $restoredBy): void {
                $orderStatus->restored_by = $restoredBy;
                $orderStatus->restored_at = now();
                $orderStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_ORDER_STATUS,
                    $actor,
                    $orderStatus,
                    ['before' => $this->auditLogService->snapshot($orderStatus)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted orderStatuses.
     *
     * @return int Number of orderStatuses restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $orderStatusIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($orderStatusIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,OrderStatus> $orderStatuses */
            $orderStatuses = OrderStatus::withTrashed()
                ->whereIn('id', $orderStatusIds)
                ->get();

            foreach ($orderStatuses as $orderStatus) {
                if ($orderStatus->trashed()) {
                    $this->restore($orderStatus, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
