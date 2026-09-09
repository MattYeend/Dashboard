<?php

namespace App\Http\Controllers;

use App\Models\Organisation;
use App\Models\OrganisationMembership;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Handles switching the current organisation for the authenticated user.
 *
 * The chosen organisation id is stored on the session and picked up by
 * App\Multitenancy\SessionOrganisationFinder on subsequent requests.
 * Ordinary members must already hold an active membership row created
 * via InvitationService::accept() before the 'switch' policy check will
 * pass. Super admins bypass that check, so this ensures they still get
 * a genuine active membership row for the organisation, rather than a
 * stray 'invited' one, so downstream member listings stay accurate.
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

        if (! $organisation->activeUsers()->whereKey($user->id)->exists()) {
            $organisation->users()->syncWithoutDetaching([
                $user->id => [
                    'status' => OrganisationMembership::STATUS_ACTIVE,
                    'joined_at' => now(),
                    'created_by' => $user->id,
                ],
            ]);
        }

        $request->session()->put('current_organisation_id', $organisation->id);

        return redirect()->route('dashboard');
    }
}
