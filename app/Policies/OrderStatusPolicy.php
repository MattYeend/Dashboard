<?php

namespace App\Policies;

use App\Models\OrderStatus;
use App\Models\User;
use App\Services\OrderStatuses\PolicyAuthorisationService;

class OrderStatusPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any order statuses.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can view the order status.
     */
    public function view(User $user, OrderStatus $orderStatus): bool
    {
        return $this->authorisationService->canView($user, $orderStatus);
    }

    /**
     * Determine whether the user can create order statuses.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can update the order status.
     */
    public function update(User $user, OrderStatus $orderStatus): bool
    {
        return $this->authorisationService->canUpdate($user, $orderStatus);
    }

    /**
     * Determine whether the user can delete the order status.
     */
    public function delete(User $user, OrderStatus $orderStatus): bool
    {
        return $this->authorisationService->canDelete($user, $orderStatus);
    }

    /**
     * Determine whether the user can restore the order status.
     */
    public function restore(User $user, OrderStatus $orderStatus): bool
    {
        return $this->authorisationService->canRestore($user, $orderStatus);
    }

    /**
     * Determine whether the user can permanently delete the order status.
     */
    public function forceDelete(User $user, OrderStatus $orderStatus): bool
    {
        return $this->authorisationService->canForceDelete($user, $orderStatus);
    }
}
