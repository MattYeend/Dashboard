<?php

declare(strict_types=1);

namespace App\Services\Payments;

use App\Actions\Payments\RefundPayment;
use App\Http\Requests\Payments\ImportPaymentRequest;
use App\Http\Requests\Payments\RefundPaymentRequest;
use App\Http\Requests\Payments\StorePaymentRequest;
use App\Http\Requests\Payments\UpdatePaymentRequest;
use App\Models\Payment;
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
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly DataPreparationService $dataPreparation,
        protected readonly RefundPayment $refundPayment,
    ) {}

    /**
     * Record a new payment.
     */
    public function store(StorePaymentRequest $request): Payment
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing payment.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment): Payment
    {
        return $this->updater->update(
            $payment,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Refund all or part of a completed payment.
     */
    public function refund(RefundPaymentRequest $request, Payment $payment): Payment
    {
        return $this->refundPayment->handle(
            $payment,
            $this->dataPreparation->prepareForRefund($request->validated()),
            $request->user()
        );
    }

    /**
     * Soft delete a payment.
     */
    public function destroy(Payment $payment, User $actor): void
    {
        $this->destructor->delete($payment, $actor->id);
    }

    /**
     * Restore a soft-deleted payment.
     */
    public function restore(int $id, User $actor): Payment
    {
        $payment = Payment::onlyTrashed()->findOrFail($id);

        return $this->restorer->restore($payment, $actor->id);
    }

    /**
     * Force delete a payment, permanently removing it from the database.
     */
    public function forceDelete(int $id, User $actor): void
    {
        $payment = Payment::onlyTrashed()->findOrFail($id);

        $this->destructor->forceDelete($payment, $actor->id);
    }

    /**
     * Bulk restore payments.
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

        $paymentsById = Payment::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get()
            ->keyBy('id');

        $restored = [];
        $skipped = [];

        foreach ($requestedIds as $id) {
            $payment = $paymentsById->get($id);

            if ($payment === null) {
                $skipped[] = $id;

                continue;
            }

            $authoriseCallback($payment);

            $this->restorer->restore($payment, $actor->id);

            $restored[] = $payment->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $skipped,
        ];
    }

    /**
     * Bulk soft delete payments. Completed payments and refunds are
     * reported in "skipped" rather than failing the whole batch.
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

        $paymentsById = Payment::with('status')
            ->whereIn('id', $requestedIds)
            ->get()
            ->keyBy('id');

        $deleted = [];
        $skipped = [];

        foreach ($requestedIds as $id) {
            $payment = $paymentsById->get($id);

            if ($payment === null || ! $this->activeChecker->canBeDeleted($payment)) {
                $skipped[] = $id;

                continue;
            }

            $authoriseCallback($payment);

            $this->destructor->delete($payment, $actor->id);

            $deleted[] = $payment->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $skipped,
        ];
    }

    /**
     * Import payments from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(ImportPaymentRequest $request): array
    {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export payments matching the given filters as a CSV download.
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
