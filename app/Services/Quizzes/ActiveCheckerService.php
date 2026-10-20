<?php

declare(strict_types=1);

namespace App\Services\Quizzes;

use App\Models\Quiz;
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
     * Check if quiz is active (not soft-deleted).
     */
    public function isActive(Quiz $quiz): bool
    {
        return ! $quiz->trashed();
    }

    /**
     * Check if quiz is soft-deleted.
     */
    public function isTrashed(Quiz $quiz): bool
    {
        return $quiz->trashed();
    }

    /**
     * Check if quiz is active and can be updated or deleted.
     */
    public function canBeModified(Quiz $quiz): bool
    {
        return $this->isActive($quiz);
    }

    /**
     * Check if quiz is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(Quiz $quiz): bool
    {
        return $this->isTrashed($quiz);
    }

    /**
     * Check if user can modify, restore or force-delete the quiz based on
     * its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Quiz $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
