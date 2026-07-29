<?php

namespace App\Services\DealStatuses;

use App\Actions\CreateResource;
use App\Models\DealStatus;
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
     * Create a new deal status.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): DealStatus
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): DealStatus {
                $dealStatusData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newDealStatus = DealStatus::create($dealStatusData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_DEAL_STATUS,
                    $actor,
                    $newDealStatus,
                    ['after' => $this->auditLogService->snapshot($newDealStatus)],
                );

                return $newDealStatus;
            });
    }
}
