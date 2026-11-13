<?php

declare(strict_types=1);

namespace App\Services\PaymentStatuses;

use App\Http\Requests\PaymentStatuses\ImportPaymentStatusRequest;
use App\Http\Requests\PaymentStatuses\StorePaymentStatusRequest;
use App\Http\Requests\PaymentStatuses\UpdatePaymentStatusRequest;
use App\Models\PaymentStatus;
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
     * Create a new payment status.
     */
    public function store(StorePaymentStatusRequest $request): PaymentStatus
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing payment status.
     */
    public function update(UpdatePaymentStatusRequest $request, PaymentStatus $paymentStatus): PaymentStatus
    {
        return $this->updater->update(
            $paymentStatus,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a payment status.
     */
    public function destroy(PaymentStatus $paymentStatus, User $actor): void
    {
        $this->destructor->delete($paymentStatus, $actor->id);
    }

    /**
     * Restore a soft-deleted payment status.
     */
    public function restore(int $id, User $actor): PaymentStatus
    {
        $paymentStatus = PaymentStatus::onlyTrashed()->findOrFail($id);

        return $this->restorer->restore($paymentStatus, $actor->id);
    }

    /**
     * Force delete a payment status, permanently removing it from the database.
     */
    public function forceDelete(int $id, User $actor): void
    {
        $paymentStatus = PaymentStatus::onlyTrashed()->findOrFail($id);

        $this->destructor->forceDelete($paymentStatus, $actor->id);
    }

    /**
     * Bulk restore payment statuses.
     *
     * @param  array<int, int>  $ids
     * @return array{restored: array<int, int>, skipped: array<int, int>}
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $paymentStatusesById = PaymentStatus::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get()
            ->keyBy('id');

        $restored = [];
        $skipped = [];

        foreach ($requestedIds as $id) {
            $paymentStatus = $paymentStatusesById->get($id);

            if ($paymentStatus === null) {
                $skipped[] = $id;

                continue;
            }

            $authoriseCallback($paymentStatus);

            $this->restorer->restore($paymentStatus, $actor->id);

            $restored[] = $paymentStatus->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $skipped,
        ];
    }

    /**
     * Bulk soft delete payment statuses.
     *
     * @param  array<int, int>  $ids
     * @return array{deleted: array<int, int>, skipped: array<int, int>}
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $paymentStatusesById = PaymentStatus::whereIn('id', $requestedIds)->get()->keyBy('id');

        $deleted = [];
        $skipped = [];

        foreach ($requestedIds as $id) {
            $paymentStatus = $paymentStatusesById->get($id);

            if ($paymentStatus === null) {
                $skipped[] = $id;

                continue;
            }

            $authoriseCallback($paymentStatus);

            $this->destructor->delete($paymentStatus, $actor->id);

            $deleted[] = $paymentStatus->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $skipped,
        ];
    }

    /**
     * Import payment statuses from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(ImportPaymentStatusRequest $request): array
    {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export payment statuses matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }

    /**
     * Expose the importer service for controller-level preview/commit actions.
     */
    public function importer(): ImporterService
    {
        return $this->importer;
    }
}
