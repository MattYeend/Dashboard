<?php

namespace App\Actions\Like;

use App\Models\Comment;
use App\Models\User;
use App\Services\Likes\DeleterService;
use Illuminate\Support\Facades\DB;

class UnlikeComment
{
    public function __construct(
        protected DeleterService $deleterService,
    ) {}

    /**
     * Unlike the given comment on behalf of the given user.
     */
    public function handle(Comment $comment, User $user): void
    {
        DB::transaction(fn () => $this->deleterService->delete($comment, $user));
    }
}
