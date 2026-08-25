<?php

namespace App\Services\InteractionLogs;

use App\Actions\UpdateResource;
use App\Models\InteractionLog;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;

class UpdaterService
{
    public function __construct(
        private readonly UpdateResource $updateResource,
        private readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Update an existing interaction log.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(InteractionLog $interactionLog, array $data, User $actor): InteractionLog
    {
        $before = $this->auditLogService->snapshot($interactionLog);

        return $this->updateResource->handle($interactionLog, $data, function (InteractionLog $interactionLog, array $data) use ($actor, $before): InteractionLog {
            $interactionLog->fill($data);
            $interactionLog->updated_by = $actor->id;
            $interactionLog->save();

            $this->auditLogService->record(
                Log::ACTION_UPDATE_INTERACTION_LOG,
                $actor,
                $interactionLog,
                ['before' => $before, 'after' => $this->auditLogService->snapshot($interactionLog)],
            );

            return $interactionLog;
        });
    }
}
