<?php

namespace App\Actions\Like;

use App\Models\Post;
use App\Models\User;
use App\Services\Likes\DeleterService;
use Illuminate\Support\Facades\DB;

class UnlikePost
{
    public function __construct(
        protected DeleterService $deleterService,
    ) {}

    /**
     * Unlike the given post on behalf of the given user.
     */
    public function handle(Post $post, User $user): void
    {
        DB::transaction(fn () => $this->deleterService->delete($post, $user));
    }
}
