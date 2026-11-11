<?php

declare(strict_types=1);

namespace App\Services\QuoteStatuses;

use App\Models\QuoteStatus;
use App\Models\User;
use App\Services\UserRoleCheckerService;

class ActiveCheckerService
{
    /**
     * Inject the required services into the active checker service.
     */
    public function __construct(
        protected readonly UserRoleCheckerService $roleChecker
    ) {}

    /**
     * Check if quote status is active (not soft-deleted).
     */
    public function isActive(QuoteStatus $quoteStatus): bool
    {
        return ! $quoteStatus->trashed();
    }

    /**
     * Check if quote status is soft-deleted.
     */
    public function isTrashed(QuoteStatus $quoteStatus): bool
    {
        return $quoteStatus->trashed();
    }

    /**
     * Check if quote status is active (not soft-deleted) and can be
     * updated/deleted.
     */
    public function canBeModified(QuoteStatus $quoteStatus): bool
    {
        return $this->isActive($quoteStatus);
    }

    /**
     * Check if the status is a protected system status.
     */
    public function isSystem(QuoteStatus $quoteStatus): bool
    {
        return $quoteStatus->isSystem();
    }

    /**
     * Check if the status can be deleted: active and not a system status.
     */
    public function canBeDeleted(QuoteStatus $quoteStatus): bool
    {
        return $this->canBeModified($quoteStatus) && ! $this->isSystem($quoteStatus);
    }

    /**
     * Check if quote status is soft-deleted and can be restored/force-deleted.
     */
    public function canBeRestoredOrForceDeleted(
        QuoteStatus $quoteStatus
    ): bool {
        return $this->isTrashed($quoteStatus);
    }

    /**
     * Check if user can modify quote status (update/delete) or
     * restore/force-delete quote status based on its active status.
     */
    public function canUserPerformAction(
        User $actor,
        string $action,
        QuoteStatus $target,
    ): bool {
        return match ($action) {
            'modify' => $this->roleChecker->isAdmin($actor) && $this->canBeModified($target),
            'delete' => $this->roleChecker->isAdmin($actor) && $this->canBeDeleted($target),
            'restoreOrForceDelete' => $this->roleChecker->isAdmin($actor) && $this->canBeRestoredOrForceDeleted($target),
            default => false,
        };
    }
}
