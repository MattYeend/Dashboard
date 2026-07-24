<?php

namespace App\Services\Addresses;

use App\Models\Address;
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
    public function isUser(
        User $user
    ): bool {
        return $this->roleChecker->isUser(
            $user
        );
    }

    /**
     * Check if user is admin or super admin.
     */
    public function isAdmin(
        User $user
    ): bool {
        return $this->roleChecker->isAdmin(
            $user
        );
    }

    /**
     * Check if address is active (not soft-deleted).
     */
    public function isActive(
        Address $address
    ): bool {
        return $this->activeChecker->isActive(
            $address
        );
    }

    /**
     * Check if address is soft-deleted.
     */
    public function isTrashed(
        Address $address
    ): bool {
        return $this->activeChecker->isTrashed(
            $address
        );
    }

    /**
     * Determine whether the user can view any addresses.
     */
    public function canViewAny(
        User $actor
    ): bool {
        return $actor->can(
            'view any addresses'
        );
    }

    /**
     * Determine whether the user can create addresses.
     */
    public function canCreate(
        User $actor
    ): bool {
        return $actor->can(
            'create addresses'
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function canView(
        User $actor,
        Address $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $actor->can('view addresses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function canUpdate(
        User $actor,
        Address $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $actor->can('edit addresses')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function canDelete(
        User $actor,
        Address $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $actor->can('delete addresses')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function canRestore(
        User $actor,
        Address $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $actor->can('restore addresses')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function canForceDelete(
        User $actor,
        Address $target
    ): bool {
        if ($this->targetOutranksActor(
            $actor,
            $target
        )) {
            return false;
        }

        return $this->activeChecker->canUserPerformAction(
            $actor,
            'restoreOrForceDelete',
            $target
        );
    }

    /**
     * Determine whether the user can import addresses.
     */
    public function canImport(
        User $actor
    ): bool {
        return $actor->can(
            'import addresses'
        );
    }

    /**
     * Determine whether the user can export addresses.
     */
    public function canExport(
        User $actor
    ): bool {
        return $actor->can(
            'export addresses'
        );
    }

    /**
     * Determine whether the target user outranks the acting user.
     *
     * A Super Admin cannot be managed by anyone other than another Super Admin.
     */
    private function targetOutranksActor(
        User $actor,
        Address $target
    ): bool {
        if ($this->roleChecker->isSuperAdmin(
            $actor
        )) {
            return false;
        }

        $owner = $target->contactable;

        if (! $owner instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin(
            $owner
        );
    }
}
