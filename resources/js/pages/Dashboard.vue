<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import StatsCards from '@/pages/Dashboard/components/StatsCards.vue';
import WidgetBoard from '@/pages/Dashboard/components/WidgetBoard.vue';
import GlobalSearch from '@/pages/Search/components/GlobalSearch.vue';
import { dashboard } from '@/routes';
import type {
    DashboardChart,
    DashboardMetric,
    DashboardPermissionsMeta,
    DashboardStats,
    DashboardWidget,
} from '@/types';

interface Props {
    stats: DashboardStats;
    widgets: DashboardWidget[];
    metrics: DashboardMetric[];
    charts: DashboardChart[];
    permissions: DashboardPermissionsMeta;
}

const props = defineProps<Props>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});
</script>

<template>
    <Head title="Dashboard" />

    <div
        class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4"
    >
        <GlobalSearch />

        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-300">Dashboard</h1>

            <a
                v-if="props.permissions.can_export"
                href="/dashboard/export"
                class="rounded border border-gray-500 px-3 py-1.5 text-sm text-gray-300 hover:bg-gray-800"
            >
                Export summary
            </a>
        </div>

        <StatsCards :stats="props.stats" />

        <WidgetBoard
            :widgets="props.widgets"
            :stats="props.stats"
            :metrics="props.metrics"
        />
    </div>
</template>
