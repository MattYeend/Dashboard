<?php

namespace App\Services\Reports;

use App\Models\Report;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * @param  Builder<Report>  $query
     * @return Builder<Report>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->where('title', 'like', "%{$search}%");
    }

    /**
     * @param  Builder<Report>  $query
     * @return Builder<Report>
     */
    public function applyType(Builder $query, ?string $type): Builder
    {
        if ($type !== null) {
            $query->where('type', $type);
        }

        return $query;
    }

    /**
     * @param  Builder<Report>  $query
     * @return Builder<Report>
     */
    public function applyFormat(Builder $query, ?string $format): Builder
    {
        if ($format !== null) {
            $query->where('format', $format);
        }

        return $query;
    }

    /**
     * @param  Builder<Report>  $query
     * @return Builder<Report>
     */
    public function applyIsScheduled(Builder $query, ?bool $isScheduled): Builder
    {
        if ($isScheduled !== null) {
            $query->where('is_scheduled', $isScheduled);
        }

        return $query;
    }

    /**
     * @param  Builder<Report>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Report>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyType($query, $filters['type'] ?? null);
        $query = $this->applyFormat($query, $filters['format'] ?? null);

        return $this->applyIsScheduled(
            $query,
            isset($filters['is_scheduled']) ? (bool) $filters['is_scheduled'] : null
        );
    }
}
