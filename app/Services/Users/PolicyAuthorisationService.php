<?php

namespace App\Services\Users;

use App\Models\User;
use App\Services\UserRoleCheckerService;
use Illuminate\Auth\Access\AuthorizationException;

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
     * Check if user is active (not soft-deleted).
     */
    public function isActive(User $user): bool
    {
        return $this->activeChecker->isActive($user);
    }

    /**
     * Check if user is soft-deleted.
     */
    public function isTrashed(User $user): bool
    {
        return $this->activeChecker->isTrashed($user);
    }

    /**
     * Determine whether the actor can view any users.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view users');
    }

    /**
     * Determine whether the actor can create users.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create users');
    }

    /**
     * Determine whether the actor can view the target user.
     */
    public function canView(User $actor, User $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view users')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the actor can update the target user.
     */
    public function canUpdate(User $actor, User $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit users')
            && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the actor can delete the target user.
     */
    public function canDelete(User $actor, User $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete users')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the actor can restore the target user.
     */
    public function canRestore(User $actor, User $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore users') &&
            $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the actor can permanently delete the target user.
     */
    public function canForceDelete(User $actor, User $target): bool
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
     * Determine whether the actor can impersonate the target user.
     *
     * Only a Super Admin may start an impersonation session, and
     * never against themselves, a trashed user, or a user who
     * outranks them.
     */
    public function canImpersonate(User $actor, User $target): bool
    {
        if ($actor->is($target) || $this->activeChecker->isTrashed($target)) {
            return false;
        }

        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('impersonate users')
            && $this->roleChecker->isSuperAdmin($actor);
    }

    /**
     * Determine whether the actor can import users.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import users');
    }

    /**
     * Determine whether the actor can export users.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export users');
    }

    /**
     * Determine whether the actor may assign the given tier role and
     * additional functional roles to the target user.
     *
     * @param  array<int, string>  $functionalRoles
     *
     * @throws AuthorizationException
     */
    public function authoriseRoleAssignment(
        User $actor,
        User $target,
        string $tierRole,
        array $functionalRoles = []
    ): void {
        if (! $actor->can('assign roles')) {
            throw new AuthorizationException('You are not permitted to assign roles.');
        }

        if ($this->targetOutranksActor($actor, $target)) {
            throw new AuthorizationException('You cannot modify the roles of a user who outranks you.');
        }

        $this->assertValidTierAndFunctionalRoles($actor, $tierRole, $functionalRoles);
    }

    /**
     * Determine whether the actor may create a new user with the given
     * tier role and additional functional roles. No target user exists
     * yet, so there is no outranking check.
     *
     * @param  array<int, string>  $functionalRoles
     *
     * @throws AuthorizationException
     */
    public function authoriseRoleAssignmentOnCreate(
        User $actor,
        string $tierRole,
        array $functionalRoles = []
    ): void {
        if (! $actor->can('create users')) {
            throw new AuthorizationException('You are not permitted to create users.');
        }

        $this->assertValidTierAndFunctionalRoles($actor, $tierRole, $functionalRoles);
    }

    /**
     * Determine whether the user can view their own profile.
     */
    public function canViewOwnProfile(User $user, User $target): bool
    {
        return $user->id === $target->id && $user->can('view own profile');
    }

    /**
     * Determine whether the user can edit their own profile.
     */
    public function canEditOwnProfile(User $user, User $target): bool
    {
        return $user->id === $target->id && $user->can('edit own profile');
    }

    /**
     * Determine whether the user can delete their own profile.
     */
    public function canDeleteOwnProfile(User $user, User $target): bool
    {
        return $user->id === $target->id && $user->can('delete own profile');
    }

    /**
     * Determine whether the user can change their own password.
     */
    public function canChangeOwnPassword(User $user, User $target): bool
    {
        return $user->id === $target->id && $user->can('change own password');
    }

    /**
     * Determine whether the user can view another user's profile.
     */
    public function canViewOtherProfile(User $user, User $target): bool
    {
        return $user->id !== $target->id && $user->can('view other profiles');
    }

    /**
     * Shared tier/functional role validation used by both the create
     * and update authorisation entry points.
     *
     * @param  array<int, string>  $functionalRoles
     *
     * @throws AuthorizationException
     */
    private function assertValidTierAndFunctionalRoles(
        User $actor,
        string $tierRole,
        array $functionalRoles
    ): void {
        if ($tierRole === 'Super Admin' && ! $this->roleChecker->isSuperAdmin($actor)) {
            throw new AuthorizationException('Only a Super Admin can grant the Super Admin role.');
        }

        if ($tierRole === 'Admin' && ! $this->roleChecker->isAdmin($actor)) {
            throw new AuthorizationException('Only an Admin or Super Admin can grant the Admin role.');
        }

        foreach ($functionalRoles as $role) {
            if (! in_array($role, User::FUNCTIONAL_ROLES, true)) {
                throw new AuthorizationException("\"{$role}\" is not a valid functional role.");
            }
        }
    }

    /**
     * Determine whether the target user outranks the acting user.
     *
     * A Super Admin cannot be managed by anyone other than another Super Admin.
     */
    private function targetOutranksActor(User $actor, User $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($target);
    }
}
