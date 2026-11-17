<?php

declare(strict_types=1);

namespace App\Services\Likes;

use App\Contracts\Likeable;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class DeleterService
{
    /**
     * Remove the given user's like from the likeable model, if one exists.
     */
    public function delete(
        Model&Likeable $likeable,
        User $user
    ): void {
        $likeable->likes()->where('user_id', $user->id)->delete();
    }
}
