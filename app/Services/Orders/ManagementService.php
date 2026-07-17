<?php

namespace App\Services\Orders;

use App\Http\Requests\Orders\StoreOrderRequest;
use App\Http\Requests\Orders\UpdateOrderRequest;
use App\Models\Order;
use App\Models\User;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer,
    ) {}

    /**
     * Create a new company order.
     */
    public function store(StoreOrderRequest $request): Order
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing order.
     */
    public function update(
        UpdateOrderRequest $request,
        Order $order
    ): Order {
        return $this->updater->update(
            $order,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a order.
     */
    public function destroy(
        Order $order,
        User $actor
    ): void {
        $this->destructor->delete($order, $actor->id);
    }

    /**
     * Restore a soft-deleted order.
     */
    public function restore(
        int $id,
        User $actor
    ): Order {
        $order = Order::withTrashed()->findOrFail($id);

        return $this->restorer->restore($order, $actor->id);
    }

    /**
     * Force delete a order, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $order = Order::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($order, $actor->id);
    }

    /**
     * Bulk restore orders.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $orders = Order::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($orders as $order) {
            /** @var Order $order */
            $authoriseCallback($order);
            $this->restorer->restore($order, $actor->id);
            $restored[] = $order->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($orders->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete orders.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $deleted = [];

        foreach ($ids as $id) {
            $order = Order::findOrFail($id);
            $authoriseCallback($order);

            $this->destructor->delete($order, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}
