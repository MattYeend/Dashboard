<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles switching the current organisation for the authenticated user.
 *
 * The chosen organisation id is stored on the session and picked up by
 * App\Multitenancy\SessionOrganisationFinder on subsequent requests.
 */
class OrganisationSwitchController extends Controller
{
    /**
     * Set the given organisation as current for the authenticated user.
     *
     * The user must already be a member of the organisation being
     * switched to; this is not a request to join one.
     */
    public function update(Request $request, Organisation $organisation): RedirectResponse
    {
        abort_unless(
            $request->user()->organisations()->whereKey($organisation->id)->exists(),
            403,
            'You do not belong to this organisation.'
        );

        $request->session()->put('current_organisation_id', $organisation->id);

        return redirect()->back();
    }
}
