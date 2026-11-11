<?php

declare(strict_types=1);

namespace App\Services\QuoteStatuses;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\QuoteStatus;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly UpdateResource $updateResource,
    ) {}

    /**
     * Update an existing quote status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        QuoteStatus $quoteStatus,
        array $data,
        int $updatedBy
    ): QuoteStatus {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($quoteStatus);

        $quoteStatusData = $this->dataPreparation->prepareForUpdate($data, $quoteStatus);

        return $this->updateResource->handle(
            $quoteStatus,
            $quoteStatusData,
            function (QuoteStatus $quoteStatus) use ($actor, $before, $updatedBy): void {
                $quoteStatus->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();

                $fresh = $quoteStatus->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_QUOTE_STATUS,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }
}