<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Displays the organisation-selection screen shown when a logged-in
 * user has no resolvable current organisation.
 *
 * This is the redirect target registered against Spatie's
 * NoCurrentTenant exception in bootstrap/app.php.
 */
class OrganisationSelectController extends Controller
{
    /**
     * Show the organisations the user belongs to, so they can pick one.
     */
    public function index(Request $request): Response
    {
        $organisations = $request->user()
            ->organisations()
            ->get(['organisations.id', 'organisations.name']);

        return Inertia::render('Organisations/Select', [
            'organisations' => $organisations,
        ]);
    }
}
