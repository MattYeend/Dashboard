<?php

namespace App\Services\Comments;

use App\Models\Post;
use App\Models\User;

class QueryService
{
    /**
     * Allow-listed sortable columns, to prevent arbitrary column names
     * (from query string input) reaching the query builder.
     *
     * @var array<int, string>
     */
    protected const SORTABLE_COLUMNS = ['id', 'created_at', 'updated_at'];

    /**
     * Get a paginated list of comments belonging to the given post.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    public function getPaginated(User $actor, Post $post, array $filters): array
    {
        $query = $post->comments()->with(['creator', 'updater']);

        if (($filters['trashed'] ?? null) === 'with') {
            $query->withTrashed();
        } elseif (($filters['trashed'] ?? null) === 'only') {
            $query->onlyTrashed();
        }

        if (! empty($filters['search'])) {
            $query->where('content', 'like', '%'.$filters['search'].'%');
        }

        $sortBy = in_array($filters['sort_by'] ?? null, self::SORTABLE_COLUMNS, true)
            ? $filters['sort_by']
            : 'created_at';

        $sortDirection = ($filters['sort_direction'] ?? null) === 'asc' ? 'asc' : 'desc';

        $perPage = min((int) ($filters['per_page'] ?? 15), 100);

        $comments = $query
            ->orderBy($sortBy, $sortDirection)
            ->paginate($perPage)
            ->withQueryString();

        return [
            'comments' => $comments,
            'post' => $post,
        ];
    }

    /**
     * Get a single comment by ID, scoped to the given post.
     */
    public function getById(Post $post, int $id): array
    {
        $comment = $post->comments()
            ->withTrashed()
            ->with(['creator', 'updater', 'deleter', 'restorer'])
            ->findOrFail($id);

        return [
            'comment' => $comment,
            'post' => $post,
        ];
    }
}
