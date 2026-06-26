<?php

namespace App\Services\Contacts;

use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use App\Models\Contact;
use App\Models\User;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     */
    public function __construct(
        protected readonly CreatorService $creator,
        protected readonly UpdaterService $updater,
        protected readonly DeleterService $destructor,
        protected readonly RestorerService $restorer,
    ) {}

    /**
     * Create a new company contact.
     */
    public function store(
        StoreContactRequest $request
    ): Contact {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing contact.
     */
    public function update(
        UpdateContactRequest $request,
        Contact $contact
    ): Contact {
        return $this->updater->update(
            $contact,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a contact.
     */
    public function destroy(Contact $contact): void
    {
        $this->destructor->delete($contact, auth()->id());
    }

    /**
     * Restore a soft-deleted contact.
     */
    public function restore(int $id): Contact
    {
        $contact = Contact::withTrashed()->findOrFail($id);

        return $this->restorer->restore($contact, auth()->id());
    }

    /**
     * Force delete a contact, permanently removing it from the
     * database.
     */
    public function forceDelete(int $id): void
    {
        $contact = Contact::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($contact, auth()->id());
    }

    /**
     * Bulk restore contacts.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $contacts = Contact::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($contacts as $contact) {
            $authoriseCallback($contact);
            $this->restorer->restore($contact, $actor->id);
            $restored[] = $contact->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($contacts->pluck('id'))
                ->values()
                ->all(),
        ];
    }

    /**
     * Bulk soft delete contacts.
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $deleted = [];

        foreach ($ids as $id) {
            $contact = Contact::findOrFail($id);
            $authoriseCallback($contact);

            $this->destructor->delete($contact, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}
