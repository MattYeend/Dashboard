<?php

namespace App\Services\Organisations;

use App\Actions\RestoreResource;
use App\Models\Log;
use App\Models\Organisation;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class RestorerService
{
    /**
     * Inject the required services into the restorer service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly RestoreResource $restoreResource,
    ) {}

    /**
     * Restore a soft-deleted organisation.
     *
     * @throws \Exception
     */
    public function restore(Organisation $organisation, int $restoredBy, ?User $actor = null): Organisation
    {
        $actor ??= User::findOrFail($restoredBy);

        return $this->restoreResource->handle(
            $organisation,
            function (Organisation $organisation) use ($actor, $restoredBy): void {
                $organisation->restored_by = $restoredBy;
                $organisation->restored_at = now();
                $organisation->save();

                $this->auditLogService->record(
                    Log::ACTION_RESTORE_ORGANISATION,
                    $actor,
                    $organisation,
                    ['before' => $this->auditLogService->snapshot($organisation)],
                );
            });
    }

    /**
     * Restore multiple soft-deleted organisations.
     *
     * @param  array<int, int>  $organisationIds
     * @return int Number of organisations restored
     *
     * @throws \Exception
     */
    public function restoreMultiple(array $organisationIds, int $restoredBy): int
    {
        $count = 0;

        DB::transaction(function () use ($organisationIds, $restoredBy, &$count) {
            $actor = User::findOrFail($restoredBy);

            /** @var Collection<int, Organisation> $organisations */
            $organisations = Organisation::withTrashed()
                ->whereIn('id', $organisationIds)
                ->get();

            foreach ($organisations as $organisation) {
                if ($organisation->trashed()) {
                    $this->restore($organisation, $restoredBy, $actor);
                    $count++;
                }
            }
        });

        return $count;
    }
}
