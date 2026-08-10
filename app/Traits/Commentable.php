<?php

namespace App\Traits;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin Model
 * @method MorphMany morphMany(string $related, string $name, string $type = null, string $id = null, string $localKey = null)
 */
trait Commentable
{
    /**
     * @return MorphMany<Comment, $this>
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->latest();
    }
}
