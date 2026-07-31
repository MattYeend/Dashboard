<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import type { DashboardStats } from '@/types';
import CompanyStatsCard from '@/pages/Dashboard/components/CompanyStatsCard.vue';
import DealStatsCard from '@/pages/Dashboard/components/DealStatsCard.vue';
import InvoiceStatsCard from '@/pages/Dashboard/components/InvoiceStatsCard.vue';
import LatestPostsCard from '@/pages/Dashboard/components/LatestPostsCard.vue';
import OrderStatsCard from '@/pages/Dashboard/components/OrderStatsCard.vue';
import TaskStatsCard from '@/pages/Dashboard/components/TaskStatsCard.vue';
import PipelineStatsCard from '@/pages/Dashboard/components/PipelineStatsCard.vue';

const props = defineProps<{
    stats: DashboardStats;
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
        <div class="grid auto-rows-min gap-4 md:grid-cols-3 xl:grid-cols-4">
            <TaskStatsCard
                :completed="props.stats.tasks.completed"
                :outstanding="props.stats.tasks.outstanding"
            />
            <CompanyStatsCard
                :total="props.stats.companies.total"
                :created-this-month="props.stats.companies.created_this_month"
            />
            <DealStatsCard
                :total="props.stats.deals.total"
                :won="props.stats.deals.won"
                :lost="props.stats.deals.lost"
            />
            <PipelineStatsCard
                :total="props.stats.pipelines.total"
                :won="props.stats.pipelines.won"
                :lost="props.stats.pipelines.lost"
            />
            <OrderStatsCard
                :total="props.stats.orders.total"
                :completed="props.stats.orders.completed"
                :outstanding="props.stats.orders.outstanding"
            />
            <InvoiceStatsCard
                :total="props.stats.invoices.total"
                :paid="props.stats.invoices.paid"
                :outstanding="props.stats.invoices.outstanding"
            />
        </div>
        <LatestPostsCard :posts="props.stats.posts" />
    </div>
</template>
