<?php

namespace App\Services\OrderStatuses;

use App\Actions\CreateResource;
use App\Models\Log;
use App\Models\OrderStatus;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Create a new orderStatus.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): OrderStatus
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): OrderStatus {
                $orderStatusData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newOrderStatus = OrderStatus::create($orderStatusData);

                $newOrderStatus->created_by = $createdBy;
                $newOrderStatus->created_at = now();
                $newOrderStatus->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_ORDER_STATUS,
                    $actor,
                    $newOrderStatus,
                    ['after' => $newOrderStatus->toArray()],
                );

                return $newOrderStatus;
            });
    }

    /**
     * Create the orderStatus record.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createContact(array $data, int $createdBy): OrderStatus
    {
        $contactData = $this->dataPreparation->prepareForCreation(
            $data, $createdBy
        );

        return OrderStatus::create($contactData);
    }
}
