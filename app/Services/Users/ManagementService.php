<?php

namespace App\Services\Users;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;

class ManagementService
{
    /**
     * Inject the required services into the management service.
     *
     * @param CreatorService $creator
     * @param UpdaterService $updater
     * @param DeleterService $destructor
     * @param RestorerService $restorer
     */
    public function __construct(
        protected CreatorService $creator,
        protected UpdaterService $updater,
        protected DeleterService $destructor,
        protected RestorerService $restorer,
    ) {
    }

    /**
     * Create a new company user.
     *
     * @param StoreUserRequest $request
     *
     * @return User
     */
    public function store(
        StoreUserRequest $request
    ): User {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing user.
     *
     * @param  UpdateUserRequest $request
     * @param  User $user
     *
     * @return User
     */
    public function update(
        UpdateUserRequest $request,
        User $user
    ): User {
        return $this->updater->update(
            $user,
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Soft delete a user.
     *
     * @param  User $user
     *
     * @return void
     */
    public function destroy(User $user): void
    {
        $this->destructor->delete($user, auth()->id());
    }

    /**
     * Restore a soft-deleted user.
     *
     * @param  int $id
     *
     * @return User
     */
    public function restore(int $id): User
    {
        $user = User::withTrashed()->findOrFail($id);
        return $this->restorer->restore($user, auth()->id());
    }

    /**
     * Force delete a user, permanently removing it from the
     * database.
     *
     * @param  int $id
     *
     * @return void
     */
    public function forceDelete(int $id): void
    {
        $user = User::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($user, auth()->id());
    }

    /**
     * Bulk restore contacts.
     *
     * @param  array $ids
     * @param  User $actor
     * @param  callable $authoriseCallback
     *
     * @return array
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $restored = [];

        foreach ($ids as $id) {
            $user = User::withTrashed()->findOrFail($id);
            $authoriseCallback($user);

            if ($user->trashed()) {
                $this->restorer->restore($user, $actor->id);
                $restored[] = $id;
            }
        }

        return $restored;
    }

    /**
     * Bulk soft delete contacts.
     *
     * @param  array $ids
     * @param  User $actor
     * @param  callable $authoriseCallback
     *
     * @return array
     */
    public function bulkDelete(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $deleted = [];

        foreach ($ids as $id) {
            $user = User::findOrFail($id);
            $authoriseCallback($user);

            $this->destructor->delete($user, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}
