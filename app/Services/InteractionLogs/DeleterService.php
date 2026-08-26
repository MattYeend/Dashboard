<?php

namespace App\Services\InteractionLogs;

use App\Actions\DeleteResource;
use App\Models\InteractionLog;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;

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
     * Soft delete an interaction log.
     *
     * @throws \Exception
     */
    public function delete(
        InteractionLog $interactionLog,
        int $deletedBy,
        ?User $actor = null
    ): bool {
        $actor ??= User::findOrFail($deletedBy);

        return $this->deleteResource->handle(
            $interactionLog,
            function (InteractionLog $interactionLog) use ($actor, $deletedBy): void {
                $interactionLog->deleted_by = $deletedBy;
                $interactionLog->deleted_at = now();
                $interactionLog->save();

                $this->auditLogService->record(
                    Log::ACTION_DELETE_INTERACTION_LOG,
                    $actor,
                    $interactionLog,
                    ['before' => $this->auditLogService->snapshot($interactionLog)],
                );
            }
        );
    }

    /**
     * Permanently delete an interaction log.
     *
     * @throws \Exception
     */
    public function forceDelete(
        InteractionLog $interactionLog,
        int $deletedBy
    ): bool {
        $actor = User::findOrFail($deletedBy);

        return $this->deleteResource->forceHandle(
            $interactionLog,
            function (InteractionLog $interactionLog) use ($actor): void {
                $this->auditLogService->record(
                    Log::ACTION_FORCE_DELETE_INTERACTION_LOG,
                    $actor,
                    $interactionLog,
                    ['before' => $this->auditLogService->snapshot($interactionLog)],
                );
            }
        );
    }
}
