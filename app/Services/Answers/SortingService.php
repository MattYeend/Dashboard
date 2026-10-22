<?php

declare(strict_types=1);

namespace App\Services\Answers;

use App\Models\Answer;
use Illuminate\Database\Eloquent\Builder;

class SortingService
{
    /**
     * Apply sorting to the query.
     *
     * Correctness is deliberately not sortable, as it would leak the key.
     *
     * @param  Builder<Answer>  $query
     * @return Builder<Answer>
     */
    public function applySorting(
        Builder $query,
        ?string $sortBy = 'position',
        ?string $sortDirection = 'asc'
    ): Builder {
        $sortBy = $sortBy ?? 'position';
        $sortDirection = strtolower($sortDirection ?? 'asc') === 'asc' ? 'asc' : 'desc';

        $query = match ($sortBy) {
            'answer' => $query->orderBy('answer', $sortDirection),
            'created_at' => $query->orderBy('created_at', $sortDirection),
            'updated_at' => $query->orderBy('updated_at', $sortDirection),
            default => $query->orderBy('position', $sortDirection),
        };

        return $query->orderBy('id');
    }

    /**
     * Get available sort fields.
     *
     * @return array<string, string>
     */
    public function getAvailableSortFields(): array
    {
        return [
            'position' => 'Position',
            'answer' => 'Answer',
            'created_at' => 'Created Date',
            'updated_at' => 'Updated Date',
        ];
    }
}
