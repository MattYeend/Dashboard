<?php

declare(strict_types=1);

namespace App\Services\DealStatuses;

use App\Actions\UpdateResource;
use App\Models\DealStatus;
use App\Models\Log;
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
     * Update an existing deal status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \DomainException When a system status would be renamed.
     * @throws \Exception
     */
    public function update(
        DealStatus $dealStatus,
        array $data,
        int $updatedBy
    ): DealStatus {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($dealStatus);

        $dealStatusData = $this->dataPreparation->prepareForUpdate($data);

        $this->ensureNotRenamed($dealStatus, $dealStatusData);

        return $this->updateResource->handle(
            $dealStatus,
            $dealStatusData,
            function (DealStatus $dealStatus) use ($actor, $before, $updatedBy): void {
                $dealStatus->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $dealStatus->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_DEAL_STATUS,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );
            });
    }

    /**
     * Refuse to rename a protected system status.
     *
     * The title is part of the workflow contract for system statuses. An
     * unchanged title is allowed so edit forms that always send it still work.
     *
     * @param  array<string, mixed>  $payload
     *
     * @throws \DomainException
     */
    protected function ensureNotRenamed(DealStatus $dealStatus, array $payload): void
    {
        if (
            $dealStatus->isSystem()
            && array_key_exists('title', $payload)
            && $payload['title'] !== $dealStatus->title
        ) {
            throw new \DomainException('System statuses cannot be renamed.');
        }
    }
}
