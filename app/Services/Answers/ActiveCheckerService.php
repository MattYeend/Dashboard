<?php

declare(strict_types=1);

namespace App\Services\Answers;

use App\Models\Answer;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class ActiveCheckerService
{
    /**
     * Inject the required services into the active checker service.
     */
    public function __construct(
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if answer is active (not soft-deleted).
     */
    public function isActive(Answer $answer): bool
    {
        return ! $answer->trashed();
    }

    /**
     * Check if answer is soft-deleted.
     */
    public function isTrashed(Answer $answer): bool
    {
        return $answer->trashed();
    }

    /**
     * Check if answer is active and can be updated or deleted.
     */
    public function canBeModified(Answer $answer): bool
    {
        return $this->isActive($answer);
    }

    /**
     * Check if answer is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(Answer $answer): bool
    {
        return $this->isTrashed($answer);
    }

    /**
     * Check if the user can perform an action on the answer.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Answer $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
