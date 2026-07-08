<?php

namespace App\Services\Orders;

use App\Actions\UpdateResource;
use App\Models\Log;
use App\Models\Order;
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
     * Update an existing order.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Order $order,
        array $data,
        int $updatedBy
    ): Order {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($order);

        $contactData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $order,
            $contactData,
            function (Order $order) use ($actor, $before): void {
                $fresh = $order->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_ORDER,
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
