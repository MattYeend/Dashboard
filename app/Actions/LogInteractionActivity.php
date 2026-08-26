<?php

namespace App\Actions;

use App\Enums\ActivityType;
use App\Models\InteractionLog;
use App\Models\User;
use App\Services\Activities\ActivityableTypeRegistryService;
use App\Services\Activities\CreatorService as ActivityCreatorService;

class LogInteractionActivity
{
    public function __construct(
        private readonly ActivityCreatorService $activityCreatorService,
        private readonly ActivityableTypeRegistryService $activityableTypeRegistryService,
    ) {}

    /**
     * Create an Activity Timeline entry for a logged interaction.
     */
    public function handle(InteractionLog $interactionLog, User $actor): void
    {
        $type = $interactionLog->type->value === 'call'
            ? ActivityType::CallLogged
            : ActivityType::EmailLogged;

        $this->activityCreatorService->create([
            'activityable_type' => $this->activityableTypeRegistryService->keyForModel($interactionLog->interactable_type),
            'activityable_id' => $interactionLog->interactable_id,
            'type' => $type,
            'description' => "{$interactionLog->type->label()} logged: {$interactionLog->subject}",
            'occurred_at' => $interactionLog->occurred_at,
        ], $actor->id);
    }
}
