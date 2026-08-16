<?php

namespace App\Services\Deals;

use App\Actions\UpdateResource;
use App\Models\Deal;
use App\Models\Log;
use App\Models\User;
use App\Notifications\DealStageChangedNotification;
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
     * Update an existing deal.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Deal $deal,
        array $data,
        int $updatedBy
    ): Deal {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($deal);
        $previousStageId = $deal->stage_id;

        $dealData = $this->dataPreparation->prepareForUpdate($data);

        return $this->updateResource->handle(
            $deal,
            $dealData,
            function (Deal $deal) use ($actor, $before, $previousStageId, $updatedBy): void {
                $deal->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
                $fresh = $deal->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_DEAL,
                    $actor,
                    $fresh,
                    [
                        'before' => $before,
                        'after' => $this->auditLogService->snapshot($fresh),
                    ],
                );

                if ($fresh->stage_id !== $previousStageId && $fresh->assigned_to) {
                    $fresh->assignee?->notify(new DealStageChangedNotification($fresh));
                }
            });
    }
}
