<?php

namespace App\Services\ApiTokens;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class QueryService
{
    /**
     * Get all tokens belonging to the given user, most recent first.
     */
    public function forUser(User $user): Collection
    {
        return $user->tokens()
            ->orderByDesc('created_at')
            ->get();
    }
}
