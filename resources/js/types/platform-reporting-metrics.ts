export interface PlatformReportingMetrics {
    organisation_count: number;
    subscription_counts: {
        active: number;
        trialing: number;
        cancelled: number;
    };
    total_active_members: number;
    signups_over_time: Record<string, number>;
}