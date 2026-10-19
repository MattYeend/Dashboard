<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Models\Lesson;
use App\Models\User;
use App\Services\UserRoleCheckerService;
use Carbon\CarbonInterface;

class ActiveCheckerService
{
    /**
     * Inject the required services into the active checker service.
     */
    public function __construct(
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if lesson is active (not soft-deleted).
     */
    public function isActive(Lesson $lesson): bool
    {
        return ! $lesson->trashed();
    }

    /**
     * Check if lesson is soft-deleted.
     */
    public function isTrashed(Lesson $lesson): bool
    {
        return $lesson->trashed();
    }

    /**
     * Check if the lesson and its course are both live to learners.
     */
    public function isPublished(Lesson $lesson): bool
    {
        $lesson->loadMissing('course');

        return $this->isLive($lesson->published_at)
            && $lesson->course !== null
            && $this->isLive($lesson->course->published_at);
    }

    /**
     * Check if lesson is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(Lesson $lesson): bool
    {
        return $this->isActive($lesson);
    }

    /**
     * Check if lesson is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(Lesson $lesson): bool
    {
        return $this->isTrashed($lesson);
    }

    /**
     * Check if user can modify lesson (update/delete) or restore/force-delete
     * lesson based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Lesson $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }

    /**
     * Determine whether a publish date has already passed.
     */
    private function isLive(?CarbonInterface $publishedAt): bool
    {
        return $publishedAt !== null && $publishedAt->lessThanOrEqualTo(now());
    }
}
