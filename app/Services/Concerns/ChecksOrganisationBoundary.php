<?php

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Model;

trait ChecksOrganisationBoundary
{
    /**
     * Determine whether the given target record belongs to the
     * organisation currently active for this request.
     *
     * This is defence-in-depth: a global scope should already prevent
     * cross-organisation records from being loaded, but routes using
     * implicit model binding can resolve a record before that scope
     * is meaningfully applied to authorisation, so this check runs
     * independently of the query layer.
     *
     * Only use on models that have an organisation_id column.
     */
    protected function belongsToCurrentOrganisation(Model $target): bool
    {
        $currentOrganisationId = session('current_organisation_id');

        if ($currentOrganisationId === null) {
            return false;
        }

        return (int) $target->organisation_id === (int) $currentOrganisationId;
    }
}
