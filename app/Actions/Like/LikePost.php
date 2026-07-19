<?php

namespace App\Actions\Like;

use App\Models\Post;
use App\Models\User;
use App\Services\Likes\CreatorService;
use Illuminate\Support\Facades\DB;

class LikePost
{
    public function __construct(
        protected CreatorService $creatorService,
    ) {}

    /**
     * Like the given post on behalf of the given user.
     */
    public function handle(Post $post, User $user): void
    {
        DB::transaction(fn () => $this->creatorService->create($post, $user));
    }
}
