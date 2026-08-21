<?php

namespace App\Policies;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenPolicy
{
    /**
     * Determine if the user can view their token list.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view api keys');
    }

    /**
     * Determine if the user can view the given token.
     */
    public function view(User $user, PersonalAccessToken $token): bool
    {
        return $user->can('view api keys') && $this->owns($user, $token);
    }

    /**
     * Determine if the user can create tokens.
     */
    public function create(User $user): bool
    {
        return $user->can('create api keys');
    }

    /**
     * Determine if the user can update the given token.
     */
    public function update(User $user, PersonalAccessToken $token): bool
    {
        return $user->can('create api keys') && $this->owns($user, $token);
    }

    /**
     * Determine if the user can revoke the given token.
     */
    public function delete(User $user, PersonalAccessToken $token): bool
    {
        return $user->can('revoke api keys') && $this->owns($user, $token);
    }

    /**
     * Whether the given token belongs to the given user.
     *
     * Sanctum's polymorphic tokenable columns are the only reliable way to
     * establish ownership here, since PersonalAccessToken has no direct
     * user_id column of its own.
     */
    private function owns(User $user, PersonalAccessToken $token): bool
    {
        return $token->tokenable_id === $user->id
            && $token->tokenable_type === $user->getMorphClass();
    }
}
