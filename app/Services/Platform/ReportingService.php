<?php

namespace App\Services\Platform;

use App\Models\Organisation;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;

class ReportingService
{
    /**
     * Build the full platform-level aggregation payload.
     *
     * @return array<string, mixed>
     */
    public function aggregate(): array
    {
        return [
            'organisation_count' => $this->organisationCount(),
            'subscription_counts' => $this->subscriptionCounts(),
            'total_active_members' => $this->totalActiveMembers(),
            'signups_over_time' => $this->signupsOverTime(),
        ];
    }

    /**
     * Count every organisation. Organisation is a central table (see
     * config('organisations.central_tables')) and is never scoped by
     * BelongsToOrganisation, so no bypass is needed here.
     */
    protected function organisationCount(): int
    {
        return Organisation::withoutGlobalScopes()->count();
    }

    /**
     * Get active/trialing/cancelled subscription counts across all users.
     *
     * @return array<string, int>
     */
    protected function subscriptionCounts(): array
    {
        return [
            'active' => Subscription::query()->active()->count(),
            'trialing' => Subscription::query()->where('status', 'trialing')->count(),
            'cancelled' => Subscription::query()->where('status', 'cancelled')->count(),
        ];
    }

    /**
     * Get the total members across every organisation, deduplicated by
     * user. User is a central table and is never scoped by
     * BelongsToOrganisation, so no bypass is needed here.
     */
    protected function totalActiveMembers(): int
    {
        return User::whereHas('organisations')->count();
    }

    /**
     * Get signups grouped by month for the trailing period.
     *
     * @return array<string, int>
     */
    protected function signupsOverTime(int $months = 12): array
    {
        $since = Carbon::now()->subMonths($months)->startOfMonth();

        return User::where('created_at', '>=', $since)
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as period, COUNT(*) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period')
            ->toArray();
    }
}
