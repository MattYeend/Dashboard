<?php

declare(strict_types=1);

namespace App\Policies;

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
     * Determine whether the user can view any quote items.
     */
    public function viewAny(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, QuoteItem $quoteItem): bool
    {
        return $this->authorisationService->canView($user, $quoteItem);
    }

    /**
     * Determine whether the user can create quote items.
     */
    public function create(User $user): bool
    {
        return $this->authorisationService->isAdmin($user);
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
     * Determine whether the user can import quote items.
     */
    public function import(User $user): bool
    {
        return $this->authorisationService->canImport($user);
    }

    /**
     * Determine whether the user can export quote items.
     */
    public function export(User $user): bool
    {
        return $this->authorisationService->canExport($user);
    }
}
