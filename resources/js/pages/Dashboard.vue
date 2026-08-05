<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import type { DashboardStats, DashboardWidget } from '@/types';
import WidgetBoard from '@/pages/Dashboard/components/WidgetBoard.vue';

const props = defineProps<{
    stats: DashboardStats;
    widgets: DashboardWidget[];
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
        <WidgetBoard :widgets="props.widgets" :stats="props.stats" />
    </div>
</template>
