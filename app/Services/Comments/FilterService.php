<?php

namespace App\Services\Comments;

use App\Models\Comment;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * @param  Builder<Comment>  $query
     * @return Builder<Comment>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where('content', 'like', "%{$search}%");
    }

    /**
     * @param  Builder<Comment>  $query
     * @return Builder<Comment>
     */
    public function applyCommentable(
        Builder $query, 
        ?string $commentableType, 
        ?int $commentableId
    ): Builder {
        if ($commentableType !== null) {
            $query->where('commentable_type', $commentableType);
        }

        if ($commentableId !== null) {
            $query->where('commentable_id', $commentableId);
        }

        return $query;
    }

    /**
     * @param  Builder<Comment>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Comment>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);

        return $this->applyCommentable(
            $query,
            $filters['commentable_type'] ?? null,
            isset($filters['commentable_id']) ? (int) $filters['commentable_id'] : null,
        );
    }
}
