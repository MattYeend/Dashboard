<?php

namespace App\Services\Orders;

use App\Models\Order;
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
     * Check if order is active (not soft-deleted).
     */
    public function isActive(Order $order): bool
    {
        return $this->activeChecker->isActive($order);
    }

    /**
     * Check if order is soft-deleted.
     */
    public function isTrashed(Order $order): bool
    {
        return $this->activeChecker->isTrashed($order);
    }

    /**
     * Determine whether the user can view any orders.
     */
    public function canViewAny(User $actor): bool
    {
        return $actor->can('view any order');
    }

    /**
     * Determine whether the user can create orders.
     */
    public function canCreate(User $actor): bool
    {
        return $actor->can('create order');
    }

    /**
     * Determine whether the user can view the model.
     * Only admins can view company contacts.
     */
    public function canView(User $actor, Order $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('view order') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function canUpdate(User $actor, Order $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('edit order') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function canDelete(User $actor, Order $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('delete order') && $this->activeChecker->canBeModified($target);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function canRestore(User $actor, Order $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('restore order')
            && $this->activeChecker->canBeRestoredOrForceDeleted($target);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function canForceDelete(User $actor, Order $target): bool
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
     * Determine whether the target user outranks the acting user.
     *
     * A Super Admin cannot be managed by anyone other than another Super Admin.
     */
    private function targetOutranksActor(User $actor, Order $target): bool
    {
        if ($this->roleChecker->isSuperAdmin($actor)) {
            return false;
        }

        $owner = $target->orderable;

        if (! $owner instanceof User) {
            return false;
        }

        return $this->roleChecker->isSuperAdmin($owner);
    }

    /**
     * Determine whether the user can assign the order to another user.
     */
    public function canAssign(User $actor, Order $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('assign task') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can change the order's status.
     */
    public function canChangeStatus(User $actor, Order $target): bool
    {
        if ($this->targetOutranksActor($actor, $target)) {
            return false;
        }

        return $actor->can('change task status') && $this->activeChecker->isActive($target);
    }

    /**
     * Determine whether the user can import orders.
     */
    public function canImport(User $actor): bool
    {
        return $actor->can('import order');
    }

    /**
     * Determine whether the user can export orders.
     */
    public function canExport(User $actor): bool
    {
        return $actor->can('export order');
    }
}
