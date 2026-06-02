<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected LogService $logService
    ) {}

    /**
     * Soft delete a contact.
     *
     * @throws \Exception
     */
    public function delete(
        Contact $contact,
        ?int $deletedBy = null
    ): bool {
        return DB::transaction(function () use ($contact, $deletedBy) {
            $actor = User::findOrFail($deletedBy);
            $contact->deleted_by = $deletedBy;
            $contact->save();

            $result = $contact->delete();

            $this->logService->logDeletion($contact, $actor, $deletedBy);

            return $result;
        });
    }

    /**
     * Force delete a contact (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Contact $contact,
        ?int $deletedBy = null
    ): bool {
        return DB::transaction(function () use ($contact, $deletedBy) {
            $actor = User::findOrFail($deletedBy);
            $this->logService->logForceDeletion($contact, $actor, $deletedBy);

            return $contact->forceDelete();
        });
    }

    /**
     * Delete multiple contacts.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $contactIds,
        ?int $deletedBy = null
    ): int {
        $count = 0;

        DB::transaction(function () use ($contactIds, $deletedBy, &$count) {
            $contacts = Contact::whereIn('id', $contactIds)->get();

            foreach ($contacts as $contact) {
                if ($this->delete($contact, $deletedBy)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
