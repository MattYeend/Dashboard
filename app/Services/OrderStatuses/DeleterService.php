<?php

namespace App\Services\OrderStatuses;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\OrderStatus;
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
     * Soft delete a orderStatus.
     *
     * @throws \Exception
     */
    public function delete(
        OrderStatus $orderStatus,
        int $deletedBy
    ): bool {

        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $orderStatus,
            function (OrderStatus $orderStatus) use ($actor, $deletedBy): void {
                $orderStatus->deleted_by = $deletedBy;
                $orderStatus->deleted_at = now();
                $orderStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_ORDER_STATUS,
                    $actor,
                    $orderStatus,
                    ['before' => $orderStatus->toArray()],
                );
            });
    }

    /**
     * Force delete a orderStatus (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        OrderStatus $orderStatus,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $orderStatus,
            function (OrderStatus $orderStatus) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_ORDER_STATUS,
                    $actor,
                    $orderStatus,
                    ['before' => $orderStatus->toArray()],
                );
            });
    }

    /**
     * Delete multiple orderStatuses.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $orderStatusIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($orderStatusIds, $deletedBy, &$count) {
            $orderStatuses = OrderStatus::whereIn('id', $orderStatusIds)->get();

            foreach ($orderStatuses as $orderStatus) {
                if ($this->delete($orderStatus, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
