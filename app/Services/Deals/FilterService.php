<?php

namespace App\Services\Deals;

use App\Models\Deal;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter to the query.
     *
     * @param  Builder<Deal>  $query
     * @return Builder<Deal>
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
     * Filter deals belonging to a specific pipeline.
     *
     * @param  Builder<Deal>  $query
     * @return Builder<Deal>
     */
    public function applyPipelineFilter(Builder $query, ?int $pipelineId): Builder
    {
        if ($pipelineId === null) {
            return $query;
        }

        return $query->where('pipeline_id', $pipelineId);
    }

    /**
     * Filter deals sitting in a specific pipeline stage.
     *
     * @param  Builder<Deal>  $query
     * @return Builder<Deal>
     */
    public function applyStageFilter(Builder $query, ?int $stageId): Builder
    {
        if ($stageId === null) {
            return $query;
        }

        return $query->where('stage_id', $stageId);
    }

    /**
     * Filter deals with a specific status.
     *
     * @param  Builder<Deal>  $query
     * @return Builder<Deal>
     */
    public function applyStatusFilter(Builder $query, ?int $statusId): Builder
    {
        if ($statusId === null) {
            return $query;
        }

        return $query->where('status_id', $statusId);
    }

    /**
     * Filter deals belonging to a specific company.
     *
     * @param  Builder<Deal>  $query
     * @return Builder<Deal>
     */
    public function applyCompanyFilter(Builder $query, ?int $companyId): Builder
    {
        if ($companyId === null) {
            return $query;
        }

        return $query->where('company_id', $companyId);
    }

    /**
     * Filter deals by currency.
     *
     * @param  Builder<Deal>  $query
     * @return Builder<Deal>
     */
    public function applyCurrencyFilter(Builder $query, ?string $currency): Builder
    {
        if ($currency === null) {
            return $query;
        }

        return $query->where('currency', $currency);
    }

    /**
     * Filter deals by a minimum and/or maximum value.
     *
     * @param  Builder<Deal>  $query
     * @return Builder<Deal>
     */
    public function applyValueRange(Builder $query, ?int $minValue, ?int $maxValue): Builder
    {
        if ($minValue !== null) {
            $query->where('value', '>=', $minValue);
        }

        if ($maxValue !== null) {
            $query->where('value', '<=', $maxValue);
        }

        return $query;
    }

    /**
     * Filter deals by a minimum and/or maximum probability.
     *
     * @param  Builder<Deal>  $query
     * @return Builder<Deal>
     */
    public function applyProbabilityRange(Builder $query, ?int $minProbability, ?int $maxProbability): Builder
    {
        if ($minProbability !== null) {
            $query->where('probability', '>=', $minProbability);
        }

        if ($maxProbability !== null) {
            $query->where('probability', '<=', $maxProbability);
        }

        return $query;
    }

    /**
     * Filter deals by an expected close date range.
     *
     * @param  Builder<Deal>  $query
     * @return Builder<Deal>
     */
    public function applyExpectedCloseDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from !== null) {
            $query->whereDate('expected_close_date', '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate('expected_close_date', '<=', $to);
        }

        return $query;
    }

    /**
     * Filter deals by a closed date range.
     *
     * @param  Builder<Deal>  $query
     * @return Builder<Deal>
     */
    public function applyClosedAtRange(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from !== null) {
            $query->whereDate('closed_at', '>=', $from);
        }

        if ($to !== null) {
            $query->whereDate('closed_at', '<=', $to);
        }

        return $query;
    }

    /**
     * Apply a tag filter to the query.
     *
     * @param  Builder<Deal>  $query
     * @return Builder<Deal>
     */
    public function applyTag(Builder $query, int|string|null $tagId): Builder
    {
        if ($tagId === null || $tagId === '') {
            return $query;
        }

        return $query->whereHas('tags', function (Builder $q) use ($tagId): void {
            $q->where('tags.id', $tagId);
        });
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Deal>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Deal>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyPipelineFilter($query, $filters['pipeline_id'] ?? null);
        $query = $this->applyStageFilter($query, $filters['stage_id'] ?? null);
        $query = $this->applyStatusFilter($query, $filters['status_id'] ?? null);
        $query = $this->applyCompanyFilter($query, $filters['company_id'] ?? null);
        $query = $this->applyCurrencyFilter($query, $filters['currency'] ?? null);
        $query = $this->applyValueRange($query, $filters['min_value'] ?? null, $filters['max_value'] ?? null);
        $query = $this->applyProbabilityRange($query, $filters['min_probability'] ?? null, $filters['max_probability'] ?? null);
        $query = $this->applyExpectedCloseDateRange($query, $filters['expected_close_from'] ?? null, $filters['expected_close_to'] ?? null);
        $query = $this->applyClosedAtRange($query, $filters['closed_from'] ?? null, $filters['closed_to'] ?? null);
        $query = $this->applyTag($query, $filters['tag_id'] ?? null);

        return $query;
    }
}
