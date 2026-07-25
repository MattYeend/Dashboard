<?php

namespace App\Policies;

use App\Models\InvoiceItem;
use App\Models\User;
use App\Services\InvoiceItems\PolicyAuthorisationService;

class InvoiceItemPolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any invoice items.
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
        InvoiceItem $invoiceItem
    ): bool {
        return $this->authorisationService->canView(
            $user,
            $invoiceItem
        );
    }

    /**
     * Determine whether the user can create invoice items.
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
        InvoiceItem $invoiceItem
    ): bool {
        return $this->authorisationService->canUpdate(
            $user,
            $invoiceItem
        );
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(
        User $user,
        InvoiceItem $invoiceItem
    ): bool {
        return $this->authorisationService->canDelete(
            $user,
            $invoiceItem
        );
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(
        User $user,
        InvoiceItem $invoiceItem
    ): bool {
        return $this->authorisationService->canRestore(
            $user,
            $invoiceItem
        );
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(
        User $user,
        InvoiceItem $invoiceItem
    ): bool {
        return $this->authorisationService->canForceDelete(
            $user,
            $invoiceItem
        );
    }

    /**
     * Determine whether the user can import invoice items.
     */
    public function import(
        User $user
    ): bool {
        return $this->authorisationService->canImport(
            $user
        );
    }

    /**
     * Determine whether the user can export invoice items.
     */
    public function export(
        User $user
    ): bool {
        return $this->authorisationService->canExport(
            $user
        );
    }
}
