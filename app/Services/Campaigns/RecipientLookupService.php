<?php

declare(strict_types=1);

namespace App\Services\Campaigns;

use App\Models\CampaignRecipient;
use App\Models\Organisation;
use Closure;

class RecipientLookupService
{
    private const TOKEN_PATTERN = '/^[A-Za-z0-9]{40}$/';

    /**
     * Find a recipient by token across tenants. The token is the credential,
     * so it must be exactly 40 alphanumeric characters before we query.
     */
    public function findByToken(string $token): ?CampaignRecipient
    {
        if (! preg_match(self::TOKEN_PATTERN, $token)) {
            return null;
        }

        return CampaignRecipient::withoutGlobalScopes()->where('token', $token)->first();
    }

    /**
     * Run a callback inside the recipient's organisation.
     *
     * The previously current organisation is always restored, even if the
     * callback throws, so no tenant state leaks between requests or jobs.
     * Returns false when the organisation no longer exists.
     */
    public function runWithinOrganisation(CampaignRecipient $recipient, Closure $callback): bool
    {
        $organisation = Organisation::withoutGlobalScopes()->find($recipient->organisation_id);

        if ($organisation === null) {
            return false;
        }

        $previous = Organisation::current();

        $organisation->makeCurrent();

        try {
            $callback();
        } finally {
            if ($previous !== null) {
                $previous->makeCurrent();
            } else {
                Organisation::forgetCurrent();
            }
        }

        return true;
    }
}
