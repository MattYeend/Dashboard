<?php

namespace App\Services\Orders;

use App\Actions\CreateResource;
use App\Models\Order;
use App\Models\Log;
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
     * Create a new order.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Order
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Order {
                $contactData = $this->dataPreparation->prepareForCreation(
                    $data,
                    $data['orderable_type'],
                    $data['orderable_id'],
                    $createdBy,
                );

                $newOrder = Order::create($contactData);

                $newOrder->created_by = $createdBy;
                $newOrder->created_at = now();
                $newOrder->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_ORDER,
                    $actor,
                    $newOrder,
                    ['after' => $newOrder->toArray()],
                );

                return $newOrder;
            });
    }
}
