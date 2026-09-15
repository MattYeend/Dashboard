<script setup lang="ts">
import type { PlatformReportingMetrics } from '@/types';
import PlatformGrowthChart from '@/pages/Platform/components/PlatformGrowthChart.vue';
import PlatformMetricsSummary from '@/pages/Platform/components/PlatformMetricsSummary.vue';
import PlatformSubscriptionBreakdown from '@/pages/Platform/components/PlatformSubscriptionBreakdown.vue';

defineProps<{ metrics: PlatformReportingMetrics }>();

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Platform Reporting', href: '/platform/reporting' },
        ],
    },
});
</script>

<template>
    <div class="space-y-6">
        <PlatformMetricsSummary
            :organisation-count="metrics.organisation_count"
            :total-active-members="metrics.total_active_members"
        />
        <PlatformSubscriptionBreakdown
            :subscription-counts="metrics.subscription_counts"
        />
        <PlatformGrowthChart :signups-over-time="metrics.signups_over_time" />
    </div>
</template>
