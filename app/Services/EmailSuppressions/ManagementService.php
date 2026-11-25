<?php

declare(strict_types=1);

namespace App\Services\EmailSuppressions;

use App\Http\Requests\EmailSuppressions\ImportEmailSuppressionRequest;
use App\Http\Requests\EmailSuppressions\StoreEmailSuppressionRequest;
use App\Http\Requests\EmailSuppressions\UpdateEmailSuppressionRequest;
use App\Models\EmailSuppression;
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
     * Create a new email suppression.
     */
    public function store(
        StoreEmailSuppressionRequest $request
    ): EmailSuppression {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing email suppression.
     */
    public function update(
        UpdateEmailSuppressionRequest $request,
        EmailSuppression $emailSuppression
    ): EmailSuppression {
        return $this->updater->update(
            $emailSuppression,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete an email suppression.
     */
    public function destroy(
        EmailSuppression $emailSuppression,
        User $actor
    ): void {
        $this->destructor->delete($emailSuppression, $actor->id, $actor);
    }

    /**
     * Restore a soft-deleted email suppression.
     */
    public function restore(
        int $id,
        User $actor
    ): EmailSuppression {
        $emailSuppression = EmailSuppression::withTrashed()->findOrFail($id);

        return $this->restorer->restore($emailSuppression, $actor->id, $actor);
    }

    /**
     * Force delete an email suppression, permanently removing it from the database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $emailSuppression = EmailSuppression::withTrashed()->findOrFail($id);

        $this->destructor->forceDelete($emailSuppression, $actor->id);
    }

    /**
     * Bulk restore email suppressions.
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

        $emailSuppressions = EmailSuppression::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($emailSuppressions as $emailSuppression) {
            /** @var EmailSuppression $emailSuppression */
            $authoriseCallback($emailSuppression);
            $this->restorer->restore($emailSuppression, $actor->id, $actor);
            $restored[] = $emailSuppression->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($emailSuppressions->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete email suppressions.
     *
     * The authorise callback runs the policy check, which rejects rows the
     * actor may not delete before the deleter is reached.
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

        $emailSuppressions = EmailSuppression::whereIn('id', $requestedIds)->get();

        $deleted = [];

        foreach ($emailSuppressions as $emailSuppression) {
            /** @var EmailSuppression $emailSuppression */
            $authoriseCallback($emailSuppression);
            $this->destructor->delete($emailSuppression, $actor->id, $actor);
            $deleted[] = $emailSuppression->id;
        }

        return [
            'deleted' => $deleted,
            'skipped' => $requestedIds
                ->diff($emailSuppressions->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Import email suppressions from an uploaded file.
     *
     * @return array{imported: int, skipped: array<int, array{row: int, reason: string}>}
     */
    public function import(
        ImportEmailSuppressionRequest $request
    ): array {
        return $this->importer->import(
            $request->file('file'),
            $request->user()->id
        );
    }

    /**
     * Export email suppressions matching the given filters as a CSV download.
     *
     * @param  array<string, mixed>  $filters
     */
    public function export(array $filters): StreamedResponse
    {
        return $this->exporter->export($filters);
    }

    /**
     * Expose the importer service for controller-level import actions
     * (preview/commit) that operate outside the request-driven import().
     */
    public function importer(): ImporterService
    {
        return $this->importer;
    }
}
