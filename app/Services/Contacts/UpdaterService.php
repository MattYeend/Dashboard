<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdaterService
{
    /**
     * Inject the required services into the updater service.
     */
    public function __construct(
        protected DataPreparationService $dataPreparation,
        protected LogService $logService
    ) {}

    /**
     * Update an existing contact.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws \Exception
     */
    public function update(
        Contact $contact,
        array $data,
        int $updatedBy
    ): Contact {
        $actor = User::findOrFail($updatedBy);

        return DB::transaction(function () use ($contact, $data, $updatedBy, $actor) {
            $this->updateContact($contact, $data, $updatedBy);
            $this->logService->logUpdate($contact, $actor, $updatedBy);

            return $contact->fresh();
        });
    }

    /**
     * Update contact data.
     *
     * @param  array<string, mixed>  $data
     */
    protected function updateContact(Contact $contact, array $data, int $updatedBy): void
    {
        $contactData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);
        $contact->update($contactData);
    }
}
