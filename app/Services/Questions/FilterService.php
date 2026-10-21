<?php

declare(strict_types=1);

namespace App\Services\Questions;

use App\Models\Question;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply search filter to query.
     *
     * @param  Builder<Question>  $query
     * @return Builder<Question>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('question', 'like', "%{$search}%")
                ->orWhere('explanation', 'like', "%{$search}%");
        });
    }

    /**
     * Apply question type filter to query.
     *
     * @param  Builder<Question>  $query
     * @return Builder<Question>
     */
    public function applyType(Builder $query, ?string $type): Builder
    {
        if ($type === null || $type === '') {
            return $query;
        }

        return $query->where('type', $type);
    }

    /**
     * Apply questionable (owner) type filter to query.
     *
     * @param  Builder<Question>  $query
     * @return Builder<Question>
     */
    public function applyQuestionableType(Builder $query, ?string $alias): Builder
    {
        if ($alias === null || $alias === '') {
            return $query;
        }

        return $query->where('questionable_type', $alias);
    }

    /**
     * Apply all filters to query.
     *
     * @param  Builder<Question>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Question>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyType($query, $filters['type'] ?? null);

        return $this->applyQuestionableType($query, $filters['questionable_type'] ?? null);
    }
}
