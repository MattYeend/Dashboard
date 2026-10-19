<?php

declare(strict_types=1);

namespace App\Services\Lessons;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Services\Concerns\ChecksOrganisationBoundary;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
    use ChecksOrganisationBoundary;

    /**
     * Inject the required services into the policy authorisation service.
     */
    public function __construct(
        protected readonly ActiveCheckerService $activeChecker,
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if user is a regular user, admin, or super admin.
     */
    public function isUser(User $user): bool
    {
        return $this->roleChecker->isUser($user);
    }

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Check if lesson is active (not soft-deleted).
     */
    public function isActive(Lesson $lesson): bool
    {
        return $this->activeChecker->isActive($lesson);
    }

    /**
     * Check if lesson is soft-deleted.
     */
    public function isTrashed(Lesson $lesson): bool
    {
        return $this->activeChecker->isTrashed($lesson);
    }

    /**
     * Determine whether the user can view any lessons.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any lessons');
    }

    /**
     * Determine whether the user can create lessons.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create lessons');
    }

    /**
     * Determine whether the user can view the lesson.
     *
     * Non-editors can only view published lessons in published courses.
     */
    public function canView(User $actor, Lesson $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        if (! $actor->can('view lessons') || ! $this->activeChecker->isActive($target)) {
            return false;
        }

        return $actor->can('edit lessons')
            || $this->activeChecker->isPublished($target);
    }

    /**
     * Determine whether the user can update the lesson.
     */
    public function canUpdate(User $actor, Lesson $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('edit lessons')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can reorder the lessons of a course.
     */
    public function canReorder(User $actor, Course $course): bool
    {
        return $this->belongsToCurrentOrganisation($course)
            && $actor->can('edit lessons');
    }

    /**
     * Determine whether the user can delete the lesson.
     */
    public function canDelete(User $actor, Lesson $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('delete lessons')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the lesson.
     */
    public function canRestore(User $actor, Lesson $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('restore lessons')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the lesson.
     */
    public function canForceDelete(User $actor, Lesson $target): bool
    {
        if (! $this->belongsToCurrentOrganisation($target)) {
            return false;
        }

        return $actor->can('force delete lessons')
            && $this->activeChecker->canUserPerformAction(
                $actor,
                'restoreOrForceDelete',
                $target
            );
    }

    /**
     * Determine whether the user can import lessons.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import lessons');
    }

    /**
     * Determine whether the user can export lessons.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export lessons');
    }
}
