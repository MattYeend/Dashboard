<?php

namespace App\Services\Organisations;

use App\Models\Log;
use App\Models\Organisation;
use App\Models\OrganisationMembership;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Access\AuthorizationException;

class SwitcherService
{
    /**
     * Inject the required services into the switcher service.
     */
    public function __construct(
        protected readonly AuditLogService $auditLogService,
    ) {}

    /**
     * Switch the given user's current organisation, verifying they
     * hold an active membership for it first.
     *
     * @throws AuthorizationException
     */
    public function switchTo(User $user, Organisation $organisation): void
    {
        $hasActiveMembership = OrganisationMembership::query()
            ->where('organisation_id', $organisation->id)
            ->where('user_id', $user->id)
            ->where('status', OrganisationMembership::STATUS_ACTIVE)
            ->exists();

        if (! $hasActiveMembership) {
            throw new AuthorizationException('You do not have an active membership for this organisation.');
        }

        session()->put('current_organisation_id', $organisation->id);

        // Logged lightly, as an access event rather than a data change.
        $this->auditLogService->record(
            Log::ACTION_SWITCH_ORGANISATION,
            $user,
            $organisation,
            [],
        );
    }
}
