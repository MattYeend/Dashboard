<?php

namespace App\Services\InvoiceStatuses;

use App\Http\Requests\InvoiceStatuses\StoreInvoiceStatusRequest;
use App\Http\Requests\InvoiceStatuses\UpdateInvoiceStatusRequest;
use App\Models\InvoiceStatus;
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
     * Create a new task status.
     */
    public function store(
        StoreInvoiceStatusRequest $request
    ): InvoiceStatus {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing task status.
     */
    public function update(
        UpdateInvoiceStatusRequest $request,
        InvoiceStatus $invoiceStatus
    ): InvoiceStatus {
        return $this->updater->update(
            $invoiceStatus,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a task status.
     */
    public function destroy(
        InvoiceStatus $invoiceStatus,
        User $actor
    ): void {
        $this->destructor->delete($invoiceStatus, $actor->id);
    }

    /**
     * Restore a soft-deleted task status.
     */
    public function restore(
        int $id,
        User $actor
    ): InvoiceStatus {
        $invoiceStatus = InvoiceStatus::withTrashed()->findOrFail($id);

        return $this->restorer->restore($invoiceStatus, $actor->id);
    }

    /**
     * Force delete a task status, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $invoiceStatus = InvoiceStatus::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($invoiceStatus, $actor->id);
    }

    /**
     * Bulk restore task statuses.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $invoiceStatuses = InvoiceStatus::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($invoiceStatuses as $invoiceStatus) {
            /** @var InvoiceStatus $invoiceStatus */
            $authoriseCallback($invoiceStatus);
            $this->restorer->restore($invoiceStatus, $actor->id);
            $restored[] = $invoiceStatus->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($invoiceStatuses->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete task statuses.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $deleted = [];

        foreach ($ids as $id) {
            $invoiceStatus = InvoiceStatus::findOrFail($id);
            $authoriseCallback($invoiceStatus);

            $this->destructor->delete($invoiceStatus, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}
