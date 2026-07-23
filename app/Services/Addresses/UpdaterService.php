<?php

namespace App\Services\Addresses;

use App\Actions\UpdateResource;
use App\Models\Address;
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
     * Update an existing address.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Address $address,
        array $data,
        int $updatedBy
    ): Address {
        $actor = User::findOrFail($updatedBy);

        $before = $this->auditLogService->snapshot($address);

        $addressData = $this->dataPreparation->prepareForUpdate(
            $data,
            $updatedBy
        );

        return $this->updateResource->handle(
            $address,
            $addressData,
            function (Address $address) use ($actor, $before): void {
                $fresh = $address->fresh();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_ADDRESS,
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
