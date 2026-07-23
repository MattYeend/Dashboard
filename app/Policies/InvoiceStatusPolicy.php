<?php

namespace App\Policies;

use App\Models\InvoiceStatus;
use App\Models\User;
use App\Services\InvoiceStatuses\PolicyAuthorisationService;

class InvoiceStatusPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any invoice statuses.
     */
    public function viewAny(
        User $user
    ): bool {
        return $this->authorisationService->isAdmin(
            $user
        );
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(
        User $user,
        InvoiceStatus $invoiceStatus
    ): bool {
        return $this->authorisationService->canView(
            $user,
            $invoiceStatus
        );
    }

    /**
     * Determine whether the user can create invoice statuses.
     */
    public function create(
        User $user
    ): bool {
        return $this->authorisationService->isAdmin(
            $user
        );
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(
        User $user,
        InvoiceStatus $invoiceStatus
    ): bool {
        return $this->authorisationService->canUpdate(
            $user,
            $invoiceStatus
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(
        User $user,
        InvoiceStatus $invoiceStatus
    ): bool {
        return $this->authorisationService->canDelete(
            $user,
            $invoiceStatus
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(
        User $user,
        InvoiceStatus $invoiceStatus
    ): bool {
        return $this->authorisationService->canRestore(
            $user,
            $invoiceStatus
        );
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(
        User $user,
        InvoiceStatus $invoiceStatus
    ): bool {
        return $this->authorisationService->canForceDelete(
            $user,
            $invoiceStatus
        );
    }
}
