<?php

namespace App\Services\InteractionLogs;

use App\Actions\CreateResource;
use App\Actions\LogInteractionActivity;
use App\Models\InteractionLog;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;

class CreatorService
{
    public function __construct(
        private readonly CreateResource $createResource,
        private readonly AuditLogService $auditLogService,
        private readonly LogInteractionActivity $logInteractionActivity,
    ) {}

    /**
     * Create a new interaction log.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data, User $actor): InteractionLog
    {
        return $this->createResource->handle($data, function (array $data) use ($actor): InteractionLog {
            $data['created_by'] = $actor->id;

            $interactionLog = InteractionLog::create($data);

            $this->auditLogService->record(
                Log::ACTION_CREATE_INTERACTION_LOG,
                $actor,
                $interactionLog,
                ['after' => $this->auditLogService->snapshot($interactionLog)],
            );

            $this->logInteractionActivity->handle($interactionLog, $actor);

            return $interactionLog;
        });
    }
}
