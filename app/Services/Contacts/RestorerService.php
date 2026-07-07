<?php

namespace App\Services\Contacts;

use App\Actions\RestoreResource;
use App\Models\Contact;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the resorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted contact.
     *
     * @throws \Exception
     */
    public function restore(
        Contact $contact,
        int $restoredBy,
        ?User $actor = null
    ): Contact {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $contact,
            function (Contact $contact) use ($actor, $restoredBy): void {
                $contact->restored_by = $restoredBy;
                $contact->restored_at = now();
                $contact->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_CONTACT,
                    $actor,
                    $contact,
                    ['before' => $this->auditLogService->snapshot($contact)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted contacts.
     *
     * @return int Number of contacts restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $contactIds,
        int $restoredBy
    ): int {
        $count = 0;

        DB::transaction(function () use ($contactIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int,Contact> $contacts */
            $contacts = Contact::withTrashed()
                ->whereIn('id', $contactIds)
                ->get();

            foreach ($contacts as $contact) {
                if ($contact->trashed()) {
                    $this->restore($contact, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
