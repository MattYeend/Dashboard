<?php

declare(strict_types=1);

namespace App\Services\Answers;

use App\Models\Answer;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Answer>  $query
     * @return Builder<Answer>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null || $search === '') {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('answer', 'like', "%{$search}%")
                ->orWhere('feedback', 'like', "%{$search}%");
        });
    }

    /**
     * Apply a question filter to the query.
     *
     * @param  Builder<Answer>  $query
     * @return Builder<Answer>
     */
    public function applyQuestion(Builder $query, mixed $questionId): Builder
    {
        if ($questionId === null || $questionId === '') {
            return $query;
        }

        return $query->where('question_id', (int) $questionId);
    }

    /**
     * Apply a correctness filter to the query.
     *
     * Callers must only pass this filter for users who may view correct
     * answers, otherwise the answer key could be inferred.
     *
     * @param  Builder<Answer>  $query
     * @return Builder<Answer>
     */
    public function applyCorrectness(Builder $query, mixed $isCorrect): Builder
    {
        if ($isCorrect === null || $isCorrect === '') {
            return $query;
        }

        return $query->where('is_correct', filter_var($isCorrect, FILTER_VALIDATE_BOOLEAN));
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Answer>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Answer>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyQuestion($query, $filters['question_id'] ?? null);

        return $this->applyCorrectness($query, $filters['is_correct'] ?? null);
    }
}
