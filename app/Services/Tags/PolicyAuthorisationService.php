<?php

namespace App\Services\Tags;

use App\Models\Tag;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class PolicyAuthorisationService
{
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
     * Check if tag is active (not soft-deleted).
     */
    public function isActive(Tag $tag): bool
    {
        return $this->activeChecker->isActive($tag);
    }

    /**
     * Check if tag is soft-deleted.
     */
    public function isTrashed(Tag $tag): bool
    {
        return $this->activeChecker->isTrashed($tag);
    }

    /**
     * Determine whether the user can view any tags.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any tags');
    }

    /**
     * Determine whether the user can create tags.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create tags');
    }

    /**
     * Determine whether the user can view the tag.
     */
    public function canView(User $actor, Tag $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view tags') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the tag.
     */
    public function canUpdate(User $actor, Tag $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('update tags') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the tag.
     */
    public function canDelete(User $actor, Tag $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete tags') && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the tag.
     */
    public function canRestore(User $actor, Tag $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore tags') && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the tag.
     */
    public function canForceDelete(User $actor, Tag $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the tag was created by a user who outranks the actor.
     *
     * Prevents admins from managing tags created by super admins.
     */
    private function targetOutranksActor(User $actor, Tag $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $creator = $target->creator;

        if (! $creator instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($creator);
    }
}
