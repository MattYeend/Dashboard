<?php

namespace App\Services\RegistrationInterests;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\RegistrationInterest;
use App\Models\User;

class RestorerService
{
    public function __construct(protected RestoreResource $restoreResource) {}

    /**
     * Restore a soft-deleted registration interest.
     */
    public function restore(int $id, User $actor): RegistrationInterest
    {
        $interest = RegistrationInterest::onlyTrashed()->findOrFail($id);

        return $this->restoreResource->handle($interest, function (RegistrationInterest $interest) use ($actor) {
            $interest->update([
                'restored_by' => $actor->id,
                'restored_at' => now(),
            ]);

            Log::log(Log::ACTION_RESTORE_REGISTRATION_INTEREST, $interest->auditSnapshot(), $actor->id);
        });
    }

    /**
     * Bulk restore multiple soft-deleted registration interests.
     *
     * @param  array<int, int>  $ids
     * @return array<int, int>
     */
    public function bulkRestore(array $ids, User $actor, callable $authorize): array
    {
        return RegistrationInterest::onlyTrashed()->whereIn('id', $ids)->get()
            ->each(function (RegistrationInterest $interest) use ($actor, $authorize) {
                $authorize($interest);

                $this->restoreResource->handle($interest, function (RegistrationInterest $interest) use ($actor) {
                    $interest->update([
                        'restored_by' => $actor->id,
                        'restored_at' => now(),
                    ]);

                    Log::log(Log::ACTION_RESTORE_REGISTRATION_INTEREST, $interest->auditSnapshot(), $actor->id);
                });
            })
            ->pluck('id')
            ->all();
    }
}
