<?php

namespace App\Services\Comments;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * @param  Builder<Comment>  $query
     * @return Builder<Comment>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'created_at',
        ?string $sortDirection = 'desc'
    ): Builder {
        $sortBy = in_array($sortBy, ['created_at', 'updated_at'], true) ? $sortBy : 'created_at';
        $sortDirection = strtolower($sortDirection ?? 'desc') === 'asc' ? 'asc' : 'desc';

        return $query->orderBy($sortBy, $sortDirection);
    }

    /**
     * @return array<string, string>
     */
    public function getAvailableSortFields(): array
    {
        return [
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
        ];
    }
}
