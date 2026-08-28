import type { DashboardStats } from '@/types';

export interface DashboardStatGroup {
    label: string;
    metrics: { key: string; label: string }[];
    values: Record<string, number>;
}

/**
 * Maps each DashboardStats group to the metric keys/labels StatsCards.vue
 * renders. Kept as a plain function (rather than a static array) so it
 * always reads from the stats prop passed in, rather than closing over a
 * stale reference.
 */
export function buildDashboardStatGroups(
    stats: DashboardStats,
): DashboardStatGroup[] {
    return [
        {
            label: 'Tasks',
            metrics: [
                { key: 'completed', label: 'Completed' },
                { key: 'outstanding', label: 'Outstanding' },
            ],
            values: stats.tasks,
        },
        {
            label: 'Companies',
            metrics: [
                { key: 'total', label: 'Total' },
                { key: 'created_this_month', label: 'New this month' },
            ],
            values: stats.companies,
        },
        {
            label: 'Deals',
            metrics: [
                { key: 'total', label: 'Total' },
                { key: 'won', label: 'Won' },
                { key: 'lost', label: 'Lost' },
            ],
            values: stats.deals,
        },
        {
            label: 'Pipelines',
            metrics: [
                { key: 'total', label: 'Total' },
                { key: 'won', label: 'Won' },
                { key: 'lost', label: 'Lost' },
            ],
            values: stats.pipelines,
        },
        {
            label: 'Orders',
            metrics: [
                { key: 'total', label: 'Total' },
                { key: 'completed', label: 'Completed' },
                { key: 'outstanding', label: 'Outstanding' },
            ],
            values: stats.orders,
        },
        {
            label: 'Invoices',
            metrics: [
                { key: 'total', label: 'Total' },
                { key: 'paid', label: 'Paid' },
                { key: 'outstanding', label: 'Outstanding' },
            ],
            values: stats.invoices,
        },
    ];
}
