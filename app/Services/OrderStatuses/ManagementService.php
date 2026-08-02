<?php

namespace App\Services\OrderStatuses;

use App\Http\Requests\OrderStatuses\ImportOrderStatusRequest;
use App\Http\Requests\OrderStatuses\StoreOrderStatusRequest;
use App\Http\Requests\OrderStatuses\UpdateOrderStatusRequest;
use App\Models\OrderStatus;
use App\Models\User;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        protected readonly ImporterService $importer,
        protected readonly ExporterService $exporter,
    ) {}

    /**
     * Create a new order status.
     */
    public function store(
        StoreOrderStatusRequest $request
    ): OrderStatus {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing order status.
     */
    public function update(
        UpdateOrderStatusRequest $request,
        OrderStatus $orderStatus
    ): OrderStatus {
        return $this->updater->update(
            $orderStatus,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a order status.
     */
    public function destroy(
        OrderStatus $orderStatus,
        User $actor
    ): void {
        $this->destructor->delete($orderStatus, $actor->id);
    }

    /**
     * Restore a soft-deleted order status.
     */
    public function restore(
        int $id,
        User $actor
    ): OrderStatus {
        $orderStatus = OrderStatus::withTrashed()->findOrFail($id);

        return $this->restorer->restore($orderStatus, $actor->id);
    }

    /**
     * Force delete a order status, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $orderStatus = OrderStatus::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($orderStatus, $actor->id);
    }

    /**
     * Bulk restore order statuses.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $orderStatuses = OrderStatus::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($orderStatuses as $orderStatus) {
            /** @var OrderStatus $orderStatus */
            $authoriseCallback($orderStatus);
            $this->restorer->restore($orderStatus, $actor->id);
            $restored[] = $orderStatus->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($orderStatuses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete order statuses.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $orderStatuses = OrderStatus::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($orderStatuses as $orderStatus) {
            /** @var OrderStatus $orderStatus */
            $authoriseCallback($orderStatus);
            $this->destructor->delete($orderStatus, $actor->id);
            $deleted[] = $orderStatus->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($orderStatuses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import order statuses from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportOrderStatusRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export order statuses matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }
}
