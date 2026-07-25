<?php

namespace App\Http\Controllers;

use App\Enums\TokenAbility;
use App\Http\Requests\ApiTokens\StoreApiTokenRequest;
use App\Http\Requests\ApiTokens\UpdateApiTokenRequest;
use App\Services\ApiTokens\CreatorService;
use App\Services\ApiTokens\DeleterService;
use App\Services\ApiTokens\QueryService;
use App\Services\ApiTokens\UpdaterService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenController extends Controller
{
    public function __construct(
        private readonly QueryService $queryService,
        private readonly CreatorService $creatorService,
        private readonly UpdaterService $updaterService,
        private readonly DeleterService $deleterService,
    ) {}

    /**
     * Display the authenticated user's tokens.
     */
    public function index(
        Request $request
    ): Response {
        $this->authorize(
            'viewAny',
            PersonalAccessToken::class
        );

        return Inertia::render('ApiTokens/Index', [
            'tokens' => $this->queryService->forUser(
                $request->user()
            ),
            'abilities' => TokenAbility::labels(),
        ]);
    }

    /**
     * Store a newly created token and return its plain-text value once.
     */
    public function store(
        StoreApiTokenRequest $request
    ): RedirectResponse {
        $newToken = $this->creatorService->create(
            $request->user(),
            $request->validated('name'),
            $request->validated('abilities'),
            $request->validated('expires_at'),
        );

        return redirect()
            ->route(
                'api-tokens.index'
            )
            ->with(
                'plainTextToken',
                $newToken->plainTextToken
            );
    }

    /**
     * Update an existing token's name, abilities, or expiry.
     */
    public function update(
        UpdateApiTokenRequest $request,
        PersonalAccessToken $apiToken
    ): RedirectResponse {
        $this->updaterService->update(
            $apiToken,
            $request->validated()
        );

        return redirect()->route(
            'api-tokens.index'
        );
    }

    /**
     * Revoke an existing token.
     */
    public function destroy(
        Request $request,
        PersonalAccessToken $apiToken
    ): RedirectResponse {
        $this->authorize(
            'delete',
            $apiToken
        );

        $this->deleterService->delete(
            $apiToken
        );

        return redirect()->route(
            'api-tokens.index'
        );
    }
}
