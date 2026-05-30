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
        return DB::transaction(function () use ($contact, $data, $updatedBy) {
            $actor = User::findOrFail($updatedBy);

            $this->updateContact($contact, $data);
            $this->logService->logUpdate($contact, $actor, $updatedBy);

            return $contact->fresh();
        });
    }

    /**
     * Update contact data.
     *
     * @param  array<string, mixed>  $data
     */
    protected function updateContact(Contact $contact, array $data): void
    {
        $contactData = $this->dataPreparation->prepareForUpdate($data);
        $contact->update($contactData);
    }
}
