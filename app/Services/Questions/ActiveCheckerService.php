<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Models\Question;
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
     * Check if question is active (not soft-deleted).
     */
    public function isActive(Question $question): bool
    {
        return ! $question->trashed();
    }

    /**
     * Check if question is soft-deleted.
     */
    public function isTrashed(Question $question): bool
    {
        return $question->trashed();
    }

    /**
     * Check if question is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(Question $question): bool
    {
        return $this->isActive($question);
    }

    /**
     * Check if question is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        Question $question
    ): bool {
        return $this->isTrashed($question);
    }

    /**
     * Check if user can modify question (update/delete) or restore/force-delete
     * question based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Question $target,
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
