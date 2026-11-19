<?php

declare(strict_types=1);

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
     * @throws \DomainException When a system status would be renamed.
     * @throws \Exception
     */
    public function update(
        OrderStatus $orderStatus,
        array $data,
        int $updatedBy
    ): OrderStatus {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($orderStatus);

        $orderStatusData = $this->dataPreparation->prepareForUpdate($data);

        $this->ensureNotRenamed($orderStatus, $orderStatusData);

        return $this->updateResource->handle(
            $orderStatus,
            $orderStatusData,
            function (OrderStatus $orderStatus) use ($actor, $before, $updatedBy): void {
                $orderStatus->forceFill([
                    'updated_by' => $updatedBy,
                ])->save();
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
    protected function ensureNotRenamed(OrderStatus $orderStatus, array $payload): void
    {
        if (
            $orderStatus->isSystem()
            && array_key_exists('title', $payload)
            && $payload['title'] !== $orderStatus->title
        ) {
            throw new \DomainException('System statuses cannot be renamed.');
        }
    }
}
