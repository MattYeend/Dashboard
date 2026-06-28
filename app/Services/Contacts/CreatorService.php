<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly DataPreparationService $dataPreparation,
        protected readonly AuditLogService $auditLogService
    ) {}

    /**
     * Create a new contact.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): Contact
    {
        $actor = User::findOrFail($createdBy);

        return DB::transaction(function () use ($data, $createdBy, $actor) {
            $contact = $this->createContact($data, $createdBy);
            $this->auditLogService->record(
                Log::ACTION_CREATE_CONTACT,
                $actor,
                $contact,
                ['before' => $contact->toArray()],
            );

            return $contact;
        });
    }

    /**
     * Create the contact record.
     *
     * @param  array<string, mixed>  $data
     */
    protected function createContact(array $data, int $createdBy): Contact
    {
        $contactData = $this->dataPreparation->prepareForCreation(
            $data,
            $data['contactable_type'],
            $data['contactable_id'],
            $createdBy,
        );

        return Contact::create($contactData);
    }
}
