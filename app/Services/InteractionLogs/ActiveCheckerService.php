<?php

namespace App\Services\InteractionLogs;

use App\Models\InteractionLog;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class ActiveCheckerService
{
    public function __construct(
        private readonly UserRoleCheckerService $userRoleCheckerService,
    ) {}

    /**
     * Determine whether the interaction log is not soft-deleted.
     */
    public function isActive(InteractionLog $interactionLog): bool
    {
        return ! $interactionLog->trashed();
    }

    /**
     * Determine whether the interaction log is soft-deleted.
     */
    public function isTrashed(InteractionLog $interactionLog): bool
    {
        return $interactionLog->trashed();
    }

    /**
     * Determine whether the interaction log can be modified (updated/deleted).
     */
    public function canBeModified(InteractionLog $interactionLog): bool
    {
        return $this->isActive($interactionLog);
    }

    /**
     * Determine whether the interaction log can be force deleted.
     */
    public function canBeForceDeleted(InteractionLog $interactionLog): bool
    {
        return $this->isTrashed($interactionLog);
    }

    /**
     * Determine whether the given actor can perform the given action on the target.
     */
    public function canUserPerformAction(User $actor, string $action, InteractionLog $target): bool
    {
        return match ($action) {
            'modify' => $this->canBeModified($target) && $this->userRoleCheckerService->isAdmin($actor),
            'forceDelete' => $this->canBeForceDeleted($target) && $this->userRoleCheckerService->isAdmin($actor),
            default => false,
        };
    }
}
