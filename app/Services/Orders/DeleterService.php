<?php

namespace App\Services\Orders;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\Order;
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
     * Soft delete a order.
     *
     * @throws \Exception
     */
    public function delete(
        Order $order,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail(
            $deletedBy
        );

        return $this->deleteResource->handle(
            $order,
            function (Order $order) use ($actor, $deletedBy): void {
                $order->deleted_by = $deletedBy;
                $order->deleted_at = now();
                $order->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_ORDER,
                    $actor,
                    $order,
                    [
                        'before' => $this->auditLogService->snapshot(
                            $order
                        ),
                    ],
                );
            }
        );
    }

    /**
     * Force delete a order (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Order $order,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail(
            $deletedBy
        );

        return $this->deleteResource->forceHandle(
            $order,
            function (Order $order) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_ORDER,
                    $actor,
                    $order,
                    [
                        'before' => $this->auditLogService->snapshot(
                            $order
                        ),
                    ],
                );
            }
        );
    }

    /**
     * Delete multiple orders.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $orderIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use (
            $orderIds,
            $deletedBy,
            &$count
        ) {
            $actor = User::findOrFail(
                $deletedBy
            );
            $orders = Order::whereIn(
                'id',
                $orderIds
            )->get();

            foreach ($orders as $order) {
                if ($this->delete(
                    $order,
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
