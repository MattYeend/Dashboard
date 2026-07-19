<?php

namespace App\Actions\Like;

use App\Models\Comment;
use App\Models\User;
use App\Services\Likes\CreatorService;
use Illuminate\Support\Facades\DB;

class LikeComment
{
    public function __construct(
        protected CreatorService $creatorService,
    ) {}

    /**
     * Like the given comment on behalf of the given user.
     */
    public function handle(Comment $comment, User $user): void
    {
        DB::transaction(fn () => $this->creatorService->create($comment, $user));
    }
}
