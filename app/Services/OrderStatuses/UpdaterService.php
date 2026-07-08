<?php

namespace App\Services\OrderStatuses;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\OrderStatus;
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
     * Update an existing order status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        OrderStatus $orderStatus,
        array $data,
        int $updatedBy
    ): OrderStatus {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($orderStatus);

        $taskStatusData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $orderStatus,
            $taskStatusData,
            function (OrderStatus $orderStatus) use ($actor, $before): void {
                $fresh = $orderStatus->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_ORDER_STATUS,
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
