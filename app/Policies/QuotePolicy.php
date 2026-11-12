<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Quote;
use App\Models\User;
use App\Services\Quotes\PolicyAuthorisationService;

class QuotePolicy
{
    /**
     * Inject the required services into the policy.
     */
    public function __construct(
        protected PolicyAuthorisationService $authorisationService
    ) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->canViewAny($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Quote $quote): bool
    {
        return $this->authorisationService->canView($user, $quote);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->canCreate($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Quote $quote): bool
    {
        return $this->authorisationService->canUpdate($user, $quote);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Quote $quote): bool
    {
        return $this->authorisationService->canDelete($user, $quote);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Quote $quote): bool
    {
        return $this->authorisationService->canRestore($user, $quote);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Quote $quote): bool
    {
        return $this->authorisationService->canForceDelete($user, $quote);
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
     * Determine whether the user can import models.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export models.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }

    /**
     * Determine whether the user can send the quote.
     */
    public function send(User $user, Quote $quote): bool
    {
        return $this->authorisationService->canSend($user, $quote);
    }

    /**
     * Determine whether the user can record the quote as accepted.
     */
    public function accept(User $user, Quote $quote): bool
    {
        return $this->authorisationService->canAccept($user, $quote);
    }

    /**
     * Determine whether the user can record the quote as declined.
     */
    public function decline(User $user, Quote $quote): bool
    {
        return $this->authorisationService->canDecline($user, $quote);
    }

    /**
     * Determine whether the user can convert the quote into an order.
     */
    public function convert(User $user, Quote $quote): bool
    {
        return $this->authorisationService->canConvert($user, $quote);
    }
}
