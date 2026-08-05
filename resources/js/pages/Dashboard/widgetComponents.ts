import type { Component } from 'vue';
import CompanyStatsCard from '@/pages/Dashboard/components/CompanyStatsCard.vue';
import DealsCreatedCard from '@/pages/Dashboard/components/DealsCreatedCard.vue';
import DealsWonCard from '@/pages/Dashboard/components/DealsWonCard.vue';
import InvoiceStatsCard from '@/pages/Dashboard/components/InvoiceStatsCard.vue';
import LatestPostsCard from '@/pages/Dashboard/components/LatestPostsCard.vue';
import OrderStatsCard from '@/pages/Dashboard/components/OrderStatsCard.vue';
import PipelinesTotalCard from '@/pages/Dashboard/components/PipelinesTotalCard.vue';
import PipelinesWonCard from '@/pages/Dashboard/components/PipelinesWonCard.vue';
import TasksCompletedCard from '@/pages/Dashboard/components/TasksCompletedCard.vue';
import TasksOutstandingCard from '@/pages/Dashboard/components/TasksOutstandingCard.vue';

export const dashboardWidgetComponents: Record<string, Component> = {
    tasks_completed: TasksCompletedCard,
    tasks_outstanding: TasksOutstandingCard,
    companies: CompanyStatsCard,
    deals_created: DealsCreatedCard,
    deals_won: DealsWonCard,
    pipelines_total: PipelinesTotalCard,
    pipelines_won: PipelinesWonCard,
    orders: OrderStatsCard,
    invoices: InvoiceStatsCard,
    latest_posts: LatestPostsCard,
};
