<?php

namespace App\Services\RegistrationInterests;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\RegistrationInterest;
use App\Models\User;

class DeleterService
{
    public function __construct(protected DeleteResource $deleteResource) {}

    /**
     * Soft delete a registration interest.
     */
    public function delete(RegistrationInterest $interest, User $actor): RegistrationInterest
    {
        $this->deleteResource->handle($interest, function (RegistrationInterest $interest) use ($actor) {
            $interest->update(['deleted_by' => $actor->id]);

            Log::log(Log::ACTION_DELETE_REGISTRATION_INTEREST, $interest->auditSnapshot(), $actor->id);
        });

        return $interest->fresh();
    }

    /**
     * Permanently delete a registration interest.
     */
    public function forceDelete(RegistrationInterest $interest, User $actor): void
    {
        $this->deleteResource->forceHandle($interest, function (RegistrationInterest $interest) use ($actor) {
            Log::log(Log::ACTION_FORCE_DELETE_REGISTRATION_INTEREST, $interest->auditSnapshot(), $actor->id);
        });
    }

    /**
     * Bulk soft delete multiple registration interests.
     *
     * @param  array<int, int>  $ids
     */
    public function bulkDelete(array $ids, User $actor, callable $authorize): void
    {
        RegistrationInterest::whereIn('id', $ids)->get()->each(function (RegistrationInterest $interest) use ($actor, $authorize) {
            $authorize($interest);

            $this->delete($interest, $actor);
        });
    }
}
