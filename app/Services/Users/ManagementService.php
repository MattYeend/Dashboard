<?php

namespace App\Services\Users;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
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
     * Create a new company user.
     */
    public function store(StoreUserRequest $request): User
    {
        return $this->creator->create(
            $request->validated(),
            $request->user()->id
        );
    }

    /**
     * Update an existing user.
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
     */
    public function destroy(
        User $user,
        User $actor
    ): void {
        $this->destructor->delete($user, $actor->id);
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore(
        int $id,
        User $actor
    ): User {
        $user = User::withTrashed()->findOrFail($id);

        return $this->restorer->restore($user, $actor->id);
    }

    /**
     * Force delete a user, permanently removing it from the
     * database.
     */
    public function forceDelete(
        int $id,
        User $actor
    ): void {
        $user = User::withTrashed()->findOrFail($id);
        $this->destructor->forceDelete($user, $actor->id);
    }

    /**
     * Bulk restore users.
     */
    public function bulkRestore(
        array $ids,
        User $actor,
        callable $authoriseCallback
    ): array {
        $requestedIds = collect($ids)->unique()->values();

        $users = User::onlyTrashed()
            ->whereIn('id', $requestedIds)
            ->get();

        $restored = [];

        foreach ($users as $user) {
            /** @var User $user */
            $authoriseCallback($user);
            $this->restorer->restore($user, $actor->id);
            $restored[] = $user->id;
        }

        return [
            'restored' => $restored,
            'skipped' => $requestedIds
                ->diff($users->pluck('id'))
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
            $user = User::findOrFail($id);
            $authoriseCallback($user);

            $this->destructor->delete($user, $actor->id);
            $deleted[] = $id;
        }

        return $deleted;
    }
}
