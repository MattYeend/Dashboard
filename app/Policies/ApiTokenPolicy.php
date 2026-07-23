<?php

namespace App\Policies;

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

class ApiTokenPolicy
{
    /**
     * Determine if the user can view their token list.
     */
    public function viewAny(
        User $user
    ): bool {
        return true;
    }

    /**
     * Determine if the user owns the given token.
     */
    public function view(
        User $user,
        PersonalAccessToken $token
    ): bool {
        return $token->tokenable_id === $user->id
            && $token->tokenable_type === $user->getMorphClass();
    }

    /**
     * Determine if the user can create tokens.
     */
    public function create(
        User $user
    ): bool {
        return true;
    }

    /**
     * Determine if the user can update the given token.
     */
    public function update(
        User $user,
        PersonalAccessToken $token
    ): bool {
        return $this->view(
            $user,
            $token
        );
    }

    /**
     * Determine if the user can revoke the given token.
     */
    public function delete(
        User $user,
        PersonalAccessToken $token
    ): bool {
        return $this->view(
            $user,
            $token
        );
    }
}
