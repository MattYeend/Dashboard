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
     * Determine whether the user can view any models.
     */
    public function viewAny(
        User $user
    ): bool {
        return $this->authorisation->canViewAny(
            $user
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(
        User $user,
        Tag $tag
    ): bool {
        return $this->authorisation->canView(
            $user,
            $tag
        );
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(
        User $user
    ): bool {
        return $this->authorisation->canCreate(
            $user
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(
        User $user,
        Tag $tag
    ): bool {
        return $this->authorisation->canUpdate(
            $user,
            $tag
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(
        User $user,
        Tag $tag
    ): bool {
        return $this->authorisation->canDelete(
            $user,
            $tag
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(
        User $user,
        Tag $tag
    ): bool {
        return $this->authorisation->canRestore(
            $user,
            $tag
        );
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(
        User $user,
        Tag $tag
    ): bool {
        return $this->authorisation->canForceDelete(
            $user,
            $tag
        );
    }
}
