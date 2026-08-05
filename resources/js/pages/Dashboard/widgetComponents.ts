import type { Component } from 'vue';
import CompanyStatsCard from '@/pages/Dashboard/components/CompanyStatsCard.vue';
import DealStatsCard from '@/pages/Dashboard/components/DealStatsCard.vue';
import InvoiceStatsCard from '@/pages/Dashboard/components/InvoiceStatsCard.vue';
import LatestPostsCard from '@/pages/Dashboard/components/LatestPostsCard.vue';
import OrderStatsCard from '@/pages/Dashboard/components/OrderStatsCard.vue';
import PipelineStatsCard from '@/pages/Dashboard/components/PipelineStatsCard.vue';
import TaskStatsCard from '@/pages/Dashboard/components/TaskStatsCard.vue';

export const dashboardWidgetComponents: Record<string, Component> = {
    tasks: TaskStatsCard,
    companies: CompanyStatsCard,
    deals: DealStatsCard,
    pipelines: PipelineStatsCard,
    orders: OrderStatsCard,
    invoices: InvoiceStatsCard,
    latest_posts: LatestPostsCard,
};