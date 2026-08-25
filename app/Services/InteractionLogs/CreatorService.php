<?php

namespace App\Services\InteractionLogs;

use App\Actions\CreateResource;
use App\Models\InteractionLog;
use App\Models\Log;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CreatorService
{
    /**
     * Inject the required services into the creator service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
        protected readonly CreateResource $createResource,
    ) {}

    /**
     * Create a new interaction log.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ModelNotFoundException
     */
    public function create(array $data, int $createdBy): InteractionLog
    {
        $actor = User::findOrFail($createdBy);

        return $this->createResource->handle(
            $data,
            function (array $data) use ($createdBy, $actor): InteractionLog {
                $interactionLog = InteractionLog::create($data);

                $interactionLog->forceFill([
                    'created_by' => $createdBy,
                ])->save();

                $this->auditLogService->record(
                    Log::ACTION_CREATE_INTERACTION_LOG,
                    $actor,
                    $interactionLog,
                    ['after' => $this->auditLogService->snapshot($interactionLog)],
                );

                return $interactionLog;
            }
        );
    }
}
