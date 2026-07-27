<?php

namespace App\Services\PipelineStages;

use App\Models\PipelineStage;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<PipelineStage>  $query
     * @return Builder<PipelineStage>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where(function (Builder $q) use ($search): void {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Apply a won-stage filter to the query.
     *
     * @param  Builder<PipelineStage>  $query
     * @return Builder<PipelineStage>
     */
    public function applyIsWon(Builder $query, ?bool $isWon): Builder
    {
        if ($isWon === null) {
            return $query;
        }

        return $query->where('is_won', $isWon);
    }

    /**
     * Apply a lost-stage filter to the query.
     *
     * @param  Builder<PipelineStage>  $query
     * @return Builder<PipelineStage>
     */
    public function applyIsLost(Builder $query, ?bool $isLost): Builder
    {
        if ($isLost === null) {
            return $query;
        }

        return $query->where('is_lost', $isLost);
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<PipelineStage>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<PipelineStage>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyIsWon($query, $filters['is_won'] ?? null);

        return $this->applyIsLost($query, $filters['is_lost'] ?? null);
    }
}