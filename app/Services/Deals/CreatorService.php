<?php

namespace App\Services\Deals;

use App\Actions\CreateResource;
use App\Models\Deal;
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
     * Create a new deal.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Deal
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Deal {
                $dealData = $this->dataPreparation->prepareForCreation($data, $createdBy);

                $newDeal = Deal::create($dealData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_DEAL,
                    $actor,
                    $newDeal,
                    ['after' => $this->auditLogService->snapshot($newDeal)],
                );

                return $newDeal;
            });
    }
}
