<?php

namespace App\Services\Industries;

use App\Actions\UpdateResource;
use App\Models\Industry;
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
     * Update an existing industry.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Industry $industry,
        array $data,
        int $updatedBy
    ): Industry {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($industry);

        $taskData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $industry,
            $taskData,
            function (Industry $industry) use ($actor, $before, $updatedBy): void {
                $fresh = $industry->fresh();

                $industry->updated_by = $updatedBy;
                $industry->updated_at = now();
                $industry->save();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_INDUSTRY,
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
