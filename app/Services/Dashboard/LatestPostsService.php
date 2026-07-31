<?php

namespace App\Services\Dashboard;

use App\Models\Post;
use Illuminate\Support\Collection;

class LatestPostsService
{
    /**
     * Get the most recently created posts, with their creator loaded.
     *
     * @return Collection<int, Post>
     */
    public function latest(int $limit = 4): Collection
    {
        return Post::query()
            ->with('creator:id,name')
            ->latest()
            ->take($limit)
            ->get(['id', 'title', 'created_at', 'created_by']);
    }
}
