<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;
use App\Services\Payments\PolicyAuthorisationService;

class PaymentPolicy
{
    /**
     * Inject the policy authorisation service.
     */
    public function __construct(
        protected readonly PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        return $this->authorisationService->canView($user, $payment);
    }

    /**
     * Determine whether the user can record payments.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }

    /**
     * Determine whether the user can update the payment.
     */
    public function update(User $user, Payment $payment): bool
    {
        return $this->authorisationService->canUpdate($user, $payment);
    }

    /**
     * Determine whether the user can delete the payment.
     */
    public function delete(User $user, Payment $payment): bool
    {
        return $this->authorisationService->canDelete($user, $payment);
    }

    /**
     * Determine whether the user can restore the payment.
     */
    public function restore(User $user, Payment $payment): bool
    {
        return $this->authorisationService->canRestore($user, $payment);
    }

    /**
     * Determine whether the user can permanently delete the payment.
     */
    public function forceDelete(User $user, Payment $payment): bool
    {
        return $this->authorisationService->canForceDelete($user, $payment);
    }

    /**
     * Determine whether the user can refund the payment.
     */
    public function refund(User $user, Payment $payment): bool
    {
        return $this->authorisationService->canRefund($user, $payment);
    }

    /**
     * Determine whether the user can bulk delete models.
     */
    public function bulkDelete(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can bulk restore models.
     */
    public function bulkRestore(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can import payments.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export payments.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }
}
