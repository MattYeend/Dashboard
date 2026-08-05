<script setup lang="ts">
import { computed } from 'vue';
import DynamicStatCard from '@/pages/Dashboard/components/DynamicStatCard.vue';
import { dashboardWidgetComponents } from '@/pages/Dashboard/widgetComponents';
import type { DashboardStats, DashboardWidget } from '@/types';

const props = defineProps<{
    widget: DashboardWidget;
    stats: DashboardStats;
}>();

const component = computed(() => dashboardWidgetComponents[props.widget.key]);

const widgetProps = computed<Record<string, unknown>>(() => {
    switch (props.widget.key) {
        case 'tasks_completed':
            return { completed: props.stats.tasks.completed };
        case 'tasks_outstanding':
            return { outstanding: props.stats.tasks.outstanding };
        case 'companies':
            return {
                total: props.stats.companies.total,
                createdThisMonth: props.stats.companies.created_this_month,
            };
        case 'deals_created':
            return { total: props.stats.deals.total };
        case 'deals_won':
            return { won: props.stats.deals.won, lost: props.stats.deals.lost };
        case 'pipelines_total':
            return { total: props.stats.pipelines.total };
        case 'pipelines_won':
            return {
                won: props.stats.pipelines.won,
                lost: props.stats.pipelines.lost,
            };
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
    <DynamicStatCard
        v-if="widget.type === 'custom'"
        :label="widget.label"
        :value="widget.value ?? 0"
    />
    <component :is="component" v-else-if="component" v-bind="widgetProps" />
</template>
