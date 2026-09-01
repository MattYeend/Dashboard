<?php

namespace App\Services\Logs;

use App\Models\Log;
use App\Services\EscapesLikeValues;
use Illuminate\Database\Eloquent\Builder;

class FilterService
{
    use EscapesLikeValues;

    /**
     * Apply a search filter against the performing user's name.
     *
     * @param  Builder<Log>  $query
     * @return Builder<Log>
     */
    public function applySearch(Builder $query, ?string $search): Builder
    {
        if ($search === null) {
            return $query;
        }

        $search = $this->escapeLikeValue($search);

        return $query->whereHas('loggedInUser', function (Builder $q) use ($search): void {
            $q->where('name', 'like', "%{$search}%");
        });
    }

    /**
     * Filter by a specific action ID.
     *
     * @param  Builder<Log>  $query
     * @return Builder<Log>
     */
    public function applyAction(Builder $query, ?int $actionId): Builder
    {
        if ($actionId === null) {
            return $query;
        }

        return $query->ofAction($actionId);
    }

    /**
     * Filter by the user who performed the action.
     *
     * @param  Builder<Log>  $query
     * @return Builder<Log>
     */
    public function applyLoggedInUser(Builder $query, ?int $loggedInUserId): Builder
    {
        if ($loggedInUserId === null) {
            return $query;
        }

        return $query->where('logged_in_user_id', $loggedInUserId);
    }

    /**
     * Filter by the user the action relates to.
     *
     * @param  Builder<Log>  $query
     * @return Builder<Log>
     */
    public function applyRelatedToUser(Builder $query, ?int $relatedToUserId): Builder
    {
        if ($relatedToUserId === null) {
            return $query;
        }

        return $query->where('related_to_user_id', $relatedToUserId);
    }

    /**
     * Filter by a created_at date range.
     *
     * @param  Builder<Log>  $query
     * @return Builder<Log>
     */
    public function applyDateRange(Builder $query, ?string $dateFrom, ?string $dateTo): Builder
    {
        if ($dateFrom !== null) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($dateTo !== null) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        return $query;
    }

    /**
     * Apply all filters to the query.
     *
     * @param  Builder<Log>  $query
     * @param  array<string, mixed>  $filters
     * @return Builder<Log>
     */
    public function applyAll(Builder $query, array $filters): Builder
    {
        $query = $this->applySearch($query, $filters['search'] ?? null);
        $query = $this->applyAction($query, isset($filters['action']) ? (int) $filters['action'] : null);
        $query = $this->applyLoggedInUser($query, isset($filters['logged_in_user_id']) ? (int) $filters['logged_in_user_id'] : null);
        $query = $this->applyRelatedToUser($query, isset($filters['related_to_user_id']) ? (int) $filters['related_to_user_id'] : null);

        return $this->applyDateRange($query, $filters['date_from'] ?? null, $filters['date_to'] ?? null);
    }
}
