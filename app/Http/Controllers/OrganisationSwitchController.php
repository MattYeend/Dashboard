<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles switching the current organisation for the authenticated user.
 *
 * The chosen organisation id is stored on the session and picked up by
 * App\Multitenancy\SessionOrganisationFinder on subsequent requests.
 * Switching also ensures the user has a membership row for the target
 * organisation, including for super admins who bypass the normal
 * membership check when authorizing the switch itself.
 */
class OrganisationSwitchController extends Controller
{
    use AuthorizesRequests;

    /**
     * Set the given organisation as current for the authenticated user.
     */
    public function update(Request $request, Organisation $organisation): RedirectResponse
    {
        $this->authorize('switch', $organisation);

        $user = $request->user();

        $organisation->users()->syncWithoutDetaching([$user->id]);

        $request->session()->put('current_organisation_id', $organisation->id);

        return redirect()->route('dashboard');
    }
}
