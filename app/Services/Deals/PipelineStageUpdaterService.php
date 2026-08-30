<?php

namespace App\Services\Deals;

use App\Actions\UpdateResource;
use App\Models\Deal;
use App\Models\Log;
use App\Models\PipelineStage;
use App\Models\User;
use App\Services\AuditLogService;

class PipelineStageUpdaterService
{
    /**
     * Inject the required action and services into the pipeline stage updater service.
     */
    public function __construct(
        protected readonly UpdateResource $updateResource,
        protected readonly AuditLogService $auditLogService
    ) {}

    /**
     * Move a deal to a different pipeline stage.
     *
     * If the target stage is linked to a closing DealStatus (is_closing), the deal's
     * status_id is updated to match and closed_at is stamped with today's date.
     * Non-closing stages leave status_id/closed_at untouched.
     */
    public function move(Deal $deal, int $stageId, int $actorId): Deal
    {
        $actor = User::findOrFail($actorId);
        $stage = PipelineStage::with('dealStatus')->findOrFail($stageId);
        $before = $deal->auditSnapshot();

        $this->updateResource->handle(
            $deal,
            ['stage_id' => $stageId],
            function () use (&$deal, $stage, $actor, $before) {
                $deal->stage_id = $stage->id;

                if ($stage->dealStatus?->is_closing) {
                    $deal->status_id = $stage->deal_status_id;
                    $deal->closed_at = now()->toDateString();
                }

                $deal->updated_by = $actor->id;
                $deal->save();

                $this->auditLogService->record(
                    Log::ACTION_UPDATE_DEAL_STAGE,
                    $actor,
                    $deal,
                    [
                        'before' => $before,
                        'after' => $deal->auditSnapshot(),
                    ]
                );
            }
        );

        return $deal->refresh();
    }
}
