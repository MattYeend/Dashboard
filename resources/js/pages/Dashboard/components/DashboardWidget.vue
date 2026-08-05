<script setup lang="ts">
import { computed } from 'vue';
import type { DashboardStats, DashboardWidget } from '@/types';
import { dashboardWidgetComponents } from '@/pages/Dashboard/widgetComponents';

const props = defineProps<{
    widget: DashboardWidget;
    stats: DashboardStats;
}>();

const component = computed(() => dashboardWidgetComponents[props.widget.key]);

const widgetProps = computed<Record<string, unknown>>(() => {
    switch (props.widget.key) {
        case 'tasks':
            return props.stats.tasks;
        case 'companies':
            return {
                total: props.stats.companies.total,
                createdThisMonth: props.stats.companies.created_this_month,
            };
        case 'deals':
            return props.stats.deals;
        case 'pipelines':
            return props.stats.pipelines;
        case 'orders':
            return props.stats.orders;
        case 'invoices':
            return props.stats.invoices;
        case 'latest_posts':
            return { posts: props.stats.posts };
        default:
            return {};
    }
});
</script>

<template>
    <component :is="component" v-if="component" v-bind="widgetProps" />
</template>