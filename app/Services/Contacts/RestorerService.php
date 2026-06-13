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
     */
    public function __construct(
        protected readonly LogService $logService
    ) {}

    /**
     * Restore a soft-deleted contact.
     *
     * @throws \Exception
     */
    public function restore(
        Contact $contact,
        ?int $restoredBy = null
    ): Contact {
        $actor = User::findOrFail($restoredBy);

        return DB::transaction(function () use ($contact, $restoredBy, $actor) {
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
