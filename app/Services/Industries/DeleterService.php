<?php

namespace App\Services\Industries;

use App\Actions\DeleteResource;
use App\Models\Industry;
use App\Models\Log;
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
     * Soft delete an industry.
     *
     * @throws \Exception
     */
    public function delete(
        Industry $industry,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $industry,
            function (Industry $industry) use ($actor, $deletedBy): void {
                $industry->deleted_by = $deletedBy;
                $industry->deleted_at = now();
                $industry->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_INDUSTRY,
                    $actor,
                    $industry,
                    ['before' => $this->auditLogService->snapshot($industry)],
                );
            });
    }

    /**
     * Force delete an industry (permanent deletion).
     *
     * @throws \Exception
     */
    public function forceDelete(
        Industry $industry,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $industry,
            function (Industry $industry) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_INDUSTRY,
                    $actor,
                    $industry,
                    ['before' => $this->auditLogService->snapshot($industry)],
                );
            });
    }

    /**
     * Delete multiple industries.
     *
     * @throws \Exception
     */
    public function deleteMultiple(
        array $industryIds,
        int $deletedBy
    ): int {
        $count = 0;

        DB::transaction(function () use (
            $industryIds,
            $deletedBy,
            &$count
        ) {
            $actor = User::findOrFail($deletedBy);
            $industries = Industry::whereIn('id', $industryIds)->get();

            foreach ($industries as $industry) {
                if ($this->delete($industry, $deletedBy, $actor)) {
                    $count++;
                }
            }
        });

        return $count;
    }
}
