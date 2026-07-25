<?php

namespace App\Services\Likes;

use App\Models\Like;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CreatorService
{
    /**
     * Create a like for the given likeable model, if one does not already exist.
     */
    public function create(
        Model $likeable,
        User $user
    ): Like {
        return Like::firstOrCreate([
            'user_id' => $user->id,
            'likeable_id' => $likeable->getKey(),
            'likeable_type' => $likeable->getMorphClass(),
        ]);
    }
}
