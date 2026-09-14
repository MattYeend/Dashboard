<?php

namespace App\Services\Organisations;

use App\Models\Organisation;
use App\Models\OrganisationMembership;
use App\Models\User;
use App\Services\UserRoleCheckerService;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

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
     * Check if user is admin or super admin.
     */
    public function isAdmin(User $user): bool
    {
        return $this->roleChecker->isAdmin($user);
    }

    /**
     * Determine whether the user can view any organisations.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any organisations');
    }

    /**
     * Determine whether the user can create organisations.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create organisations');
    }

    /**
     * Determine whether the user can view the organisation.
     */
    public function canView(User $actor, Organisation $target): bool
    {
        return $actor->can('view organisations')
            && $this->activeChecker->isActive($target)
            && $this->isActiveMember($actor, $target);
    }

    /**
     * Determine whether the user can update the organisation.
     */
    public function canUpdate(User $actor, Organisation $target): bool
    {
        return $actor->can('edit organisations')
            && $this->activeChecker->isActive($target)
            && $this->isActiveMember($actor, $target);
    }

    /**
     * Determine whether the user can manage the organisation's settings
     * (branding, preferences).
     *
     * Grants access to either an app-wide holder of the
     * 'manage organisation settings' permission, or a member holding an
     * owner/admin role within this specific organisation - mirroring
     * how canViewBilling() treats org-level management authority as
     * distinct from global Spatie admin rank.
     */
    public function canManageSettings(User $actor, Organisation $target): bool
    {
        return $this->activeChecker->isActive($target)
            && $this->isActiveMember($actor, $target)
            && ($actor->can('manage organisation settings') || $target->hasManagementRoleFor($actor));
    }

    /**
     * Determine whether the user can delete the organisation.
     */
    public function canDelete(User $actor, Organisation $target): bool
    {
        return $actor->can('delete organisations')
            && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the organisation.
     */
    public function canRestore(User $actor, Organisation $target): bool
    {
        return $actor->can('restore organisations')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the organisation.
     */
    public function canForceDelete(User $actor, Organisation $target): bool
    {
        return $this->activeChecker->canUserPerformAction($actor, 'restoreOrForceDelete', $target);
    }

    /**
     * Determine whether the actor can view billing for the given organisation.
     *
     * Requires active membership first - admin rank alone does not grant
     * billing visibility into an organisation the actor doesn't belong to.
     */
    public function canViewBilling(User $actor, Organisation $organisation): bool
    {
        return $this->isActiveMember($actor, $organisation)
            && ($this->isAdmin($actor) || $organisation->hasBillingRoleFor($actor));
    }

    /**
     * Determine whether the actor can manage billing for the given organisation.
     */
    public function canManageBilling(User $actor, Organisation $organisation): bool
    {
        return $this->canViewBilling($actor, $organisation);
    }

    /**
     * Determine whether the user can switch into the organisation.
     */
    public function canSwitch(User $actor, Organisation $target): bool
    {
        return $this->activeChecker->isActive($target)
            && $this->isActiveMember($actor, $target);
    }

    /**
     * Determine whether the user can remove a member from the organisation.
     */
    public function canRemoveMember(User $actor, Organisation $target): bool
    {
        return $actor->can('remove organisation members')
            && $this->activeChecker->isActive($target)
            && $this->isActiveMember($actor, $target);
    }

    /**
     * Determine whether the user can invite members into the organisation.
     */
    public function canInvite(User $actor, Organisation $target): bool
    {
        return $actor->can('invite members')
            && $this->activeChecker->isActive($target)
            && $this->isActiveMember($actor, $target);
    }

    /**
     * Determine whether the actor holds an active membership in the
     * given organisation.
     *
     * Super admin rank does not bypass this check - organisation
     * membership is a hard boundary, independent of role rank.
     */
    private function isActiveMember(User $actor, Organisation $target): bool
    {
        return $target->users()
            ->wherePivot('status', OrganisationMembership::STATUS_ACTIVE)
            ->whereKey($actor->id)
            ->exists();
    }

    /**
     * Determine whether the actor can invite a new member at the given role,
     * i.e. the base invite permission passes and the chosen role does not
     * outrank the actor's own highest role within the organisation.
     */
    public function canInviteWithRole(User $actor, Organisation $target, string $invitedRole): bool
    {
        if (! $this->canInvite($actor, $target)) {
            return false;
        }

        $ranks = config('organisation-roles.ranks');
        $invitedRank = $ranks[$invitedRole] ?? null;

        if ($invitedRank === null) {
            return false;
        }

        return $invitedRank >= $this->highestRoleRank($actor, $target);
    }

    /**
     * Get the roles the actor is permitted to assign when inviting a new
     * member into the organisation - every Spatie role scoped to that
     * organisation's permissions team, excluding anything that outranks
     * the actor's own highest role.
     *
     * @return Collection<int, array{id: int, name: string}>
     */
    public function assignableRolesFor(User $actor, Organisation $organisation): Collection
    {
        $ranks = config('organisation-roles.ranks');
        $actorRank = $this->highestRoleRank($actor, $organisation);

        $registrar = app(PermissionRegistrar::class);
        $previousTeamId = $registrar->getPermissionsTeamId();

        $registrar->setPermissionsTeamId($organisation->id);
        $roles = Role::query()->where('guard_name', 'web')->get(['id', 'name']);
        $registrar->setPermissionsTeamId($previousTeamId);

        return $roles
            ->filter(fn (Role $role) => ($ranks[$role->name] ?? PHP_INT_MAX) >= $actorRank)
            ->map(fn (Role $role) => ['id' => $role->id, 'name' => $role->name])
            ->values();
    }

    /**
     * Get the actor's highest-ranking (lowest-numbered) role within the
     * given organisation's permissions team.
     */
    private function highestRoleRank(User $actor, Organisation $organisation): int
    {
        $ranks = config('organisation-roles.ranks');

        $registrar = app(PermissionRegistrar::class);
        $previousTeamId = $registrar->getPermissionsTeamId();

        $registrar->setPermissionsTeamId($organisation->id);
        $actor->unsetRelation('roles');
        $actorRanks = $actor->getRoleNames()
            ->map(fn (string $roleName) => $ranks[$roleName] ?? PHP_INT_MAX)
            ->all();
        $actor->unsetRelation('roles');
        $registrar->setPermissionsTeamId($previousTeamId);

        return $actorRanks === [] ? PHP_INT_MAX : min($actorRanks);
    }
}
