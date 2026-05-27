<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     *
     * @param  DataPreparationService $dataPreparation
     * @param  LogService $logService
     */
    public function __construct(
        protected DataPreparationService $dataPreparation,
        protected LogService $logService
    ) {
    }

    /**
     * Create a new contact.
     *
     * @param  array<string, mixed> $data
     * @param  int $createdBy
     *
     * @return Contact
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Contact
    {
        $actor = User::findOrFail($createdBy);

        return DB::transaction(function () use ($data, $createdBy, $actor) {
            $contact = $this->createContact($data);
            $this->logService->logCreation($contact, $actor, $createdBy);

            return $contact;
        });
    }

    /**
     * Create the contact record.
     *
     * @param  array<string, mixed> $data
     *
     * @return Contact
     */
    protected function createContact(array $data): Contact
    {
        $contactData = $this->dataPreparation->prepareForCreation(
            $data,
            $data['contactable_type'],
            $data['contactable_id']
        );

        return Contact::create($contactData);
    }
}
