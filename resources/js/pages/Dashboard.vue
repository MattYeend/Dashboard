<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import WidgetBoard from '@/pages/Dashboard/components/WidgetBoard.vue';
import { dashboard } from '@/routes';
import type { DashboardMetric, DashboardStats, DashboardWidget } from '@/types';

const props = defineProps<{
    stats: DashboardStats;
    widgets: DashboardWidget[];
    metrics: DashboardMetric[];
}>();

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
        class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
    >
        <WidgetBoard
            :widgets="props.widgets"
            :stats="props.stats"
            :metrics="props.metrics"
        />
    </div>
</template>
