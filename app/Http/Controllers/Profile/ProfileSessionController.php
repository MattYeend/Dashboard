<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\DestroyProfileSessionsRequest;
use App\Services\Profile\SessionManagementService;
use Illuminate\Http\RedirectResponse;

class ProfileSessionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly SessionManagementService $sessionManagementService
    ) {}

    /**
     * Sign out every other session for the authenticated user. Authorisation
     * is handled by DestroyProfileSessionsRequest::authorize().
     */
    public function destroy(DestroyProfileSessionsRequest $request): RedirectResponse
    {
        $this->sessionManagementService->revokeOthers(
            $request->user(), 
            $request->session()->getId()
        );

        return redirect()->route('profile.edit')->with('status', 'sessions-revoked');
    }
}
