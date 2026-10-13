<?php

declare(strict_types=1);

namespace App\Services\Courses;

use App\Models\Course;
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
     * Check if course is active (not soft-deleted).
     */
    public function isActive(Course $course): bool
    {
        return $this->activeChecker->isActive($course);
    }

    /**
     * Check if course is soft-deleted.
     */
    public function isTrashed(Course $course): bool
    {
        return $this->activeChecker->isTrashed($course);
    }

    /**
     * Determine whether the user can view any courses.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any course');
    }

    /**
     * Determine whether the user can create courses.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create course');
    }

    /**
     * Determine whether the user can view the course.
     */
    public function canView(User $actor, Course $target): bool
    {
        if ($this->isBlocked($actor, $target)) {
            return false;
        }

        return $actor->can('view course')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the course.
     */
    public function canUpdate(User $actor, Course $target): bool
    {
        if ($this->isBlocked($actor, $target)) {
            return false;
        }

        return $actor->can('edit course')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the course.
     */
    public function canDelete(User $actor, Course $target): bool
    {
        if ($this->isBlocked($actor, $target)) {
            return false;
        }

        return $actor->can('delete course')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the course.
     */
    public function canRestore(User $actor, Course $target): bool
    {
        if ($this->isBlocked($actor, $target)) {
            return false;
        }

        return $actor->can('restore course')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the course.
     */
    public function canForceDelete(User $actor, Course $target): bool
    {
        if ($this->isBlocked($actor, $target)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the user can import courses.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import course');
    }

    /**
     * Determine whether the user can export courses.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export course');
    }

    /**
     * Determine whether the actor is blocked from the target by
     * organisation boundary or role rank.
     */
    private function isBlocked(User $actor, Course $target): bool
    {
        return ! $this->belongsToCurrentOrganisation($target)
            || $this->targetOutranksActor($actor, $target);
    }

    /**
     * Determine whether the course was created by a user who outranks the actor.
     *
     * Prevents admins from managing courses created by super admins.
     */
    private function targetOutranksActor(User $actor, Course $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $creator = $target->loadMissing('creator')->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($creator);
    }
}
