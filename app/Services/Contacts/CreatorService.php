<?php

namespace App\Services\Contacts;

use App\Actions\CreateResource;
use App\Models\Contact;
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
     * Create a new contact.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(
        array $data,
        int $createdBy
    ): Contact {
        $actor = User::findOrFail(
            $createdBy
        );

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): Contact {
                $contactData = $this->dataPreparation->prepareForCreation(
                    $data,
                    $data['contactable_type'],
                    $data['contactable_id'],
                    $createdBy,
                );

                $newContact = Contact::create($contactData);

                $this->auditLogService->record(
                    Log::ACTION_CREATE_CONTACT,
                    $actor,
                    $newContact,
                    [
                        'after' => $this->auditLogService->snapshot(
                            $newContact
                        ),
                    ],
                );

                return $newContact;
            }
        );
    }
}
