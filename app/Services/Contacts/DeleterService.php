<?php

namespace App\Services\Contacts;

use App\Actions\DeleteResource;
use App\Models\Contact;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete a contact.
     *
     * @throws \Exception
     */
    public function delete(
        Contact $contact,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $contact,
            function (Contact $contact) use ($actor, $deletedBy): void {
                $contact->deleted_by = $deletedBy;
                $contact->deleted_at = now();
                $contact->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_CONTACT,
                    $actor,
                    $contact,
                    ['before' => $contact->toArray()],
                );
            });
    }

    /**
     * Force delete a contact (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Contact $contact,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $contact,
            function (Contact $contact) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_CONTACT,
                    $actor,
                    $contact,
                    ['before' => $contact->toArray()],
                );
            });
    }

    /**
     * Delete multiple contacts.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $contactIds,
        int $deletedBy
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
