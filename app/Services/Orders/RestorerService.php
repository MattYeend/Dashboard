<?php

namespace App\Services\Orders;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\Order;
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
     * Restore a soft-deleted order.
     *
     * @throws \Exception
     */
    public function restore(
        Order $order,
        int $restoredBy,
        ?User $actor = null
    ): Order {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $order,
            function (Order $order) use ($actor, $restoredBy): void {
                $order->restored_by = $restoredBy;
                $order->restored_at = now();
                $order->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_ORDER,
                    $actor,
                    $order,
                    ['before' => $this->auditLogService->snapshot($order)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted orders.
     *
     * @return int Number of orders restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $orderIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use (
            $orderIds,
            $restoredBy,
            &$count
        ) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,Order> $orders */
            $orders = Order::withTrashed()
                ->whereIn('id', $orderIds)
                ->get();

            foreach ($orders as $order) {
                if ($order->trashed()) {
                    $this->restore(
                        $order,
                        $restoredBy,
                        $actor
                    );
                    $count++;
                }
            }
        });

        return $count;
    }
}
