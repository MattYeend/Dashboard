<?php

declare(strict_types=1);

namespace App\Services\Courses;

use App\Models\Course;
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
     * Check if course is active (not soft-deleted).
     */
    public function isActive(Course $course): bool
    {
        return ! $course->trashed();
    }

    /**
     * Check if course is soft-deleted.
     */
    public function isTrashed(Course $course): bool
    {
        return $course->trashed();
    }

    /**
     * Check if course is active and can be updated or deleted.
     */
    public function canBeModified(Course $course): bool
    {
        return $this->isActive($course);
    }

    /**
     * Check if course is soft-deleted and can be restored or force-deleted.
     */
    public function canBeRestoredOrForceDeleted(Course $course): bool
    {
        return $this->isTrashed($course);
    }

    /**
     * Check if the user can perform an action on the course.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        Course $target
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}