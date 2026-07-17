<?php

namespace App\Services\Plans;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Plan;
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
     * Update an existing plan.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Plan $plan,
        array $data,
        int $updatedBy
    ): Plan {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($plan);

        $planData = $this->dataPreparation->prepareForUpdate(
            $data,
            $updatedBy,
            $plan->id
        );

        return $this->updateResource->handle(
            $plan,
            $planData,
            function (Plan $plan) use ($actor, $before): void {
                $fresh = $plan->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_PLAN,
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
