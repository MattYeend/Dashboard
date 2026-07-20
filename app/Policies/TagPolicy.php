<?php

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;
use App\Services\Tags\PolicyAuthorisationService;

class TagPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected readonly PolicyAuthorisationService $authorisation
    ) {}

    /**
     * Determine whether the user can view any tags.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisation->isAdmin($user);
    }

    /**
     * Determine whether the user can view the tag.
     */
    public function view(User $user, Tag $tag): bool
    {
        return $this->authorisation->canView($user, $tag);
    }

    /**
     * Determine whether the user can create tags.
     */
    public function create(User $user): bool
    {
        return $this->authorisation->isAdmin($user);
    }

    /**
     * Determine whether the user can update the tag.
     */
    public function update(User $user, Tag $tag): bool
    {
        return $this->authorisation->canUpdate($user, $tag);
    }

    /**
     * Determine whether the user can delete the tag.
     */
    public function delete(User $user, Tag $tag): bool
    {
        return $this->authorisation->canDelete($user, $tag);
    }

    /**
     * Determine whether the user can restore the tag.
     */
    public function restore(User $user, Tag $tag): bool
    {
        return $this->authorisation->canRestore($user, $tag);
    }

    /**
     * Determine whether the user can permanently delete the tag.
     */
    public function forceDelete(User $user, Tag $tag): bool
    {
        return $this->authorisation->canForceDelete($user, $tag);
    }
}
