<?php

namespace App\Notifications;

use App\Models\Pipeline;

class PipelineStageAssignedNotification extends BaseNotification
{
    /**
     * @param  Pipeline  $pipeline  The pipeline the user has been assigned to.
     */
    public function __construct(protected Pipeline $pipeline) {}

    /**
     * Machine-readable category used for filtering/icons in the UI.
     */
    protected function type(): string
    {
        return 'pipeline_stage_assigned';
    }

    /**
     * Heading shown in the notifications list.
     */
    protected function title(): string
    {
        return 'Pipeline stage assigned';
    }

    /**
     * Body text shown in the notifications list.
     */
    protected function body(): string
    {
        return "You've been assigned to: {$this->pipeline->title}";
    }

    /**
     * URL the user is taken to when they click the notification.
     */
    protected function actionUrl(): ?string
    {
        return route('pipelines.show', $this->pipeline->id);
    }

    /**
     * Related model class, used to link the notification back to its subject.
     */
    protected function subjectType(): ?string
    {
        return Pipeline::class;
    }

    /**
     * Primary key of the related model.
     */
    protected function subjectId(): ?int
    {
        return $this->pipeline->id;
    }
}
