<?php

namespace App\Notifications;

use App\Models\Deal;

class DealStageChangedNotification extends BaseNotification
{
    /**
     * @param Deal $deal The deal that has changed stage.
     */
    public function __construct(protected Deal $deal) {}

    /**
     * Machine-readable category used for filtering/icons in the UI.
     */
    protected function type(): string
    {
        return 'deal_stage_changed';
    }

    /**
     * Heading shown in the notifications list.
     */
    protected function title(): string
    {
        return 'Deal stage updated';
    }

    /**
     * Body text shown in the notifications list.
     */
    protected function body(): string
    {
        return "{$this->deal->title} moved to {$this->deal->stage->title}";
    }

    /**
     * URL the user is taken to when they click the notification.
     */
    protected function actionUrl(): ?string
    {
        return route('deals.show', $this->deal->id);
    }

    /**
     * Related model class, used to link the notification back to its subject.
     */
    protected function subjectType(): ?string
    {
        return Deal::class;
    }

    /**
     * Primary key of the related model.
     */
    protected function subjectId(): ?int
    {
        return $this->deal->id;
    }
}