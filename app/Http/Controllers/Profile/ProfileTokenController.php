<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\StoreProfileTokenRequest;
use App\Services\Profile\TokenManagementService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProfileTokenController extends Controller
{
    use AuthorizesRequests;

    /**
     * Create a new controller instance.
     */
    public function __construct(
        private readonly TokenManagementService $tokenManagementService
    ) {}

    /**
     * Create a personal API token. The plain text value is flashed once.
     * Authorisation is handled by StoreProfileTokenRequest::authorize(),
     * via the existing ApiTokenPolicy.
     */
    public function store(StoreProfileTokenRequest $request): RedirectResponse
    {
        $plainText = $this->tokenManagementService->create(
            $request->user(),
            $request->validated('name'),
            $request->validated('abilities'),
            (int) $request->validated('expires_in_days'),
        );

        return redirect()->route('profile.edit')->with('new_token', $plainText);
    }

    /**
     * Revoke one of the authenticated user's own tokens. The token is
     * fetched scoped to the user first, then checked against the existing
     * ApiTokenPolicy — never trust a route parameter alone for ownership.
     */
    public function destroy(Request $request, int $tokenId): RedirectResponse
    {
        $token = $this->tokenManagementService->find($request->user(), $tokenId);

        abort_if($token === null, 404);

        $this->authorize('delete', $token);

        $this->tokenManagementService->revoke($request->user(), $token);

        return redirect()->route('profile.edit')->with('status', 'token-revoked');
    }
}
