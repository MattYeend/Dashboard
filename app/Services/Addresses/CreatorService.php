<?php

namespace App\Services\Addresses;

use App\Actions\CreateResource;
use App\Models\Address;
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
     * Create a new address.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Address
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Address {
                $contactData = $this->dataPreparation->prepareForCreation(
                    $data,
                    $data['addressable_type'],
                    $data['addressable_id'],
                    $createdBy,
                );

                $newContact = Address::create($contactData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_ADDRESS,
                    $actor,
                    $newContact,
                    ['after' => $this->auditLogService->snapshot($newContact)],
                );

                return $newContact;
            });
    }
}
