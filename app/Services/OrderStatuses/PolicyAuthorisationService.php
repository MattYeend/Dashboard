<?php

namespace App\Services\OrderStatuses;

use App\Models\OrderStatus;
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
     * Check if orderStatus is active (not soft-deleted).
     */
    public function isActive(OrderStatus $orderStatus): bool
    {
        return $this->activeChecker->isActive($orderStatus);
    }

    /**
     * Check if orderStatus is soft-deleted.
     */
    public function isTrashed(OrderStatus $orderStatus): bool
    {
        return $this->activeChecker->isTrashed($orderStatus);
    }

    /**
     * Determine whether the user can view any order statuses.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view order statuses');
    }

    /**
     * Determine whether the user can create order statuses.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create order statuses');
    }

    /**
     * Determine whether the user can view the order status.
     */
    public function canView(User $actor, OrderStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view order statuses') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the order status.
     */
    public function canUpdate(User $actor, OrderStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit order statuses') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the order status.
     */
    public function canDelete(User $actor, OrderStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete order statuses') && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the order status.
     */
    public function canRestore(User $actor, OrderStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore order statuses') && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the order status.
     */
    public function canForceDelete(User $actor, OrderStatus $target): bool
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
     * Determine whether the user can assign the order status.
     */
    public function canAssign(User $actor, OrderStatus $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign order statuses') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can import order statuses.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import order statuses');
    }

    /**
     * Determine whether the user can export order statuses.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export order statuses');
    }

    /**
     * Determine whether the order status was created by a user who outranks the actor.
     *
     * Prevents admins from managing order statuses created by super admins.
     */
    private function targetOutranksActor(User $actor, OrderStatus $target): bool
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
