<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\User;
use App\Services\QuoteItems\PolicyAuthorisationService;

class QuoteItemPolicy
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
    public function view(User $user, QuoteItem $quoteItem): bool
    {
        return $this->authorisationService->canView($user, $quoteItem);
    }

    /**
     * Determine whether the user can add a line to the given quote.
     */
    public function create(User $user, Quote $quote): bool
    {
        return $this->authorisationService->canCreate($user, $quote);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, QuoteItem $quoteItem): bool
    {
        return $this->authorisationService->canUpdate($user, $quoteItem);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, QuoteItem $quoteItem): bool
    {
        return $this->authorisationService->canDelete($user, $quoteItem);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, QuoteItem $quoteItem): bool
    {
        return $this->authorisationService->canRestore($user, $quoteItem);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, QuoteItem $quoteItem): bool
    {
        return $this->authorisationService->canForceDelete($user, $quoteItem);
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
     * Determine whether the user can import lines into the given quote.
     */
    public function import(User $user, Quote $quote): bool
    {
        return $this->authorisationService->canImport($user, $quote);
    }

    /**
     * Determine whether the user can export the given quote's lines.
     */
    public function export(User $user, Quote $quote): bool
    {
        return $this->authorisationService->canExport($user, $quote);
    }
}
