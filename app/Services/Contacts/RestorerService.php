<?php

namespace App\Services\Contacts;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
class RestorerService
{
    /**
     * Inject the required services into the resorer service.
     *
     * @param LogService $logService
     */
    public function __construct(
        protected LogService $logService
    ) {
    }

    /**
     * Restore a soft-deleted contact.
     *
     * @param  Contact $contact
     * @param  int|null $restoredBy
     *
     * @return Contact
     *
     * @throws \Exception
     */
    public function restore(
        Contact $contact,
        ?int $restoredBy = null
    ): Contact {
        return DB::transaction(function () use ($contact, $restoredBy) {
            $actor = User::findOrFail($restoredBy);

            $contact->restored_by = $restoredBy;
            $contact->restored_at = now();
            $contact->save();

            $contact->restore();

            $this->logService->logRestoration($contact, $actor, $restoredBy);

            return $contact->fresh();
        });
    }

    /**
     * Restore multiple soft-deleted contacts.
     *
     * @param  array $contactIds
     * @param  int|null $restoredBy
     *
     * @return int Number of contacts restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(
        array $contactIds,
        ?int $restoredBy = null
    ): int {
        $count = 0;

        DB::transaction(function () use ($contactIds, $restoredBy, &$count) {
            /** @var Collection<int,Contact> $contacts */
            $contacts = Contact::withTrashed()
                ->whereIn('id', $contactIds)
                ->get();

            foreach ($contacts as $contact) {
                if ($contact->trashed()) {
                    $this->restore($contact, $restoredBy);
                    $count++;
                }
            }
        });

        return $count;
    }
}
