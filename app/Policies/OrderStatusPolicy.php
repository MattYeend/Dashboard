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
     * Determine whether the user can view any model.
     */
    public function viewAny(
        User $user
    ): bool {
        return $this->authorisationService->canViewAny(
            $user
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(
        User $user,
        OrderStatus $orderStatus
    ): bool {
        return $this->authorisationService->canView(
            $user,
            $orderStatus
        );
    }

    /**
     * Determine whether the user can create model.
     */
    public function create(
        User $user
    ): bool {
        return $this->authorisationService->canCreate(
            $user
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(
        User $user,
        OrderStatus $orderStatus
    ): bool {
        return $this->authorisationService->canUpdate(
            $user,
            $orderStatus
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(
        User $user,
        OrderStatus $orderStatus
    ): bool {
        return $this->authorisationService->canDelete(
            $user,
            $orderStatus
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(
        User $user,
        OrderStatus $orderStatus
    ): bool {
        return $this->authorisationService->canRestore(
            $user,
            $orderStatus
        );
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(
        User $user,
        OrderStatus $orderStatus
    ): bool {
        return $this->authorisationService->canForceDelete(
            $user,
            $orderStatus
        );
    }

    /**
     * Determine whether the user can assign the order status.
     */
    public function assign(
        User $user,
        OrderStatus $orderStatus
    ): bool {
        return $this->authorisationService->canAssign(
            $user,
            $orderStatus
        );
    }

    /**
     * Determine whether the user can import models.
     */
    public function import(
        User $user
    ): bool {
        return $this->authorisationService->canImport(
            $user
        );
    }

    /**
     * Determine whether the user can export models.
     */
    public function export(
        User $user
    ): bool {
        return $this->authorisationService->canExport(
            $user
        );
    }
}
