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
        protected readonly DataPreparationService $dataPreparation,
        protected readonly LogService $logService
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

        $before = $this->logService->captureSnapshot($contact);

        return DB::transaction(function () use ($contact, $data, $updatedBy, $actor, $before) {
            $this->updateContact($contact, $data, $updatedBy);
            $this->logService->logUpdate($contact, $actor, $updatedBy, $before);

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
