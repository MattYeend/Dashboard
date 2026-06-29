<?php

namespace App\Services\Contacts;

use App\Actions\UpdateResource;
use App\Models\Contact;
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

        $before = $this->auditLogService->snapshot($contact);

        $contactData = $this->dataPreparation->prepareForUpdate($data, $updatedBy);

        return $this->updateResource->handle(
            $contact,
            $contactData,
            function (Contact $contact) use ($actor, $before, $updatedBy): void {
                $fresh = $contact->fresh();

                $contact->updated_by = $updatedBy;
                $contact->updated_at = now();
                $contact->save();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_CONTACT,
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
