<?php

namespace App\Services\Organisations;

use App\Actions\DeleteResource;
use App\Models\Log;
use App\Models\Organisation;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class DeleterService
{
    /**
     * Inject the required services into the deleter service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly DeleteResource $deleteResource,
    ) {}

    /**
     * Soft delete an organisation.
     *
     * @throws \Exception
     */
    public function delete(Organisation $organisation, int $deletedBy, ?User $actor = null): bool
    {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $organisation,
            function (Organisation $organisation) use ($actor, $deletedBy): void {
                $organisation->deleted_by = $deletedBy;
                $organisation->deleted_at = now();
                $organisation->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_ORGANISATION,
                    $actor,
                    $organisation,
                    ['before' => $this->auditLogService->snapshot($organisation)],
                );
            });
    }

    /**
     * Force delete an organisation (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(Organisation $organisation, int $deletedBy): bool
    {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $organisation,
            function (Organisation $organisation) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_ORGANISATION,
                    $actor,
                    $organisation,
                    ['before' => $this->auditLogService->snapshot($organisation)],
                );
            });
    }

    /**
     * Delete multiple organisations.
     *
     * @param  array<int, int>  $organisationIds
     *
     * @throws \Exception
     */
    public function deleteMultiple(array $organisationIds, int $deletedBy): int
    {
        $count = 0;

        DB::transaction(function () use ($organisationIds, $deletedBy, &$count) {
            $actor = User::findOrFail($deletedBy);
            $organisations = Organisation::whereIn('id', $organisationIds)->get();

            foreach ($organisations as $organisation) {
                if ($this->delete($organisation, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
