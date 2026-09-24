<?php

namespace App\Services\Profile;

use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class DataPreparationService
{
    /**
     * Create a new service instance.
     */
    public function __construct(
        private readonly FormatterService $formatterService,
        private readonly SessionManagementService $sessionManagementService,
        private readonly TokenManagementService $tokenManagementService,
    ) {}

    /**
     * Data for the profile show page.
     *
     * @return array<string, mixed>
     */
    public function forShow(User $user): array
    {
        return ['user' => $this->formatterService->format($user)];
    }

    /**
     * Data for the profile edit page. Whether the token section renders at
     * all is driven by the existing ApiTokenPolicy's create ability.
     *
     * @return array<string, mixed>
     */
    public function forEdit(User $user, Request $request): array
    {
        $canManageTokens = $user->can('create', PersonalAccessToken::class);

        return [
            'user' => $this->formatterService->format($user),
            'sessions' => $this->sessionManagementService
                ->listFor($user, $request->session()->getId())
                ->values(),
            'canManageTokens' => $canManageTokens,
            'tokens' => $canManageTokens ? $this->tokenManagementService->listFor($user)->values() : [],
            'tokenAbilities' => config('profile.token_abilities', []),
            'tokenLifetimes' => config('profile.token_lifetimes_days', []),
            'newToken' => $request->session()->get('new_token'),
        ];
    }
}
