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
        protected CreatorService $creator,
        protected UpdaterService $updater,
        protected DeleterService $destructor,
        protected RestorerService $restorer,
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
        $restored = [];

        foreach ($ids as $id) {
            $contact = Contact::withTrashed()->findOrFail($id);
            $authoriseCallback($contact);

            if ($contact->trashed()) {
                $this->restorer->restore($contact, $actor->id);
                $restored[] = $id;
            }
        }

        return $restored;
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
