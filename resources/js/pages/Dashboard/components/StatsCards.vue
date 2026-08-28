<script setup lang="ts">
import type { DashboardStats } from '@/types';

interface Props {
    stats: DashboardStats;
}

const props = defineProps<Props>();

const cards = [
    {
        label: 'Tasks',
        metrics: [
            { key: 'completed', label: 'Completed' },
            { key: 'outstanding', label: 'Outstanding' },
        ],
        values: props.stats.tasks,
    },
    {
        label: 'Companies',
        metrics: [
            { key: 'total', label: 'Total' },
            { key: 'created_this_month', label: 'New this month' },
        ],
        values: props.stats.companies,
    },
    {
        label: 'Deals',
        metrics: [
            { key: 'total', label: 'Total' },
            { key: 'won', label: 'Won' },
            { key: 'lost', label: 'Lost' },
        ],
        values: props.stats.deals,
    },
    {
        label: 'Pipelines',
        metrics: [
            { key: 'total', label: 'Total' },
            { key: 'won', label: 'Won' },
            { key: 'lost', label: 'Lost' },
        ],
        values: props.stats.pipelines,
    },
    {
        label: 'Orders',
        metrics: [
            { key: 'total', label: 'Total' },
            { key: 'completed', label: 'Completed' },
            { key: 'outstanding', label: 'Outstanding' },
        ],
        values: props.stats.orders,
    },
    {
        label: 'Invoices',
        metrics: [
            { key: 'total', label: 'Total' },
            { key: 'paid', label: 'Paid' },
            { key: 'outstanding', label: 'Outstanding' },
        ],
        values: props.stats.invoices,
    },
] as const;
</script>

<template>
    <div class="space-y-6">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div
                v-for="card in cards"
                :key="card.label"
                class="rounded border border-gray-500 p-4"
            >
                <p class="mb-2 text-xs text-gray-400">{{ card.label }}</p>

                <dl class="grid grid-cols-2 gap-2">
                    <div v-for="metric in card.metrics" :key="metric.key">
                        <dt class="text-xs text-gray-400">{{ metric.label }}</dt>
                        <dd class="text-lg font-semibold text-gray-300">
                            {{ (card.values as Record<string, number>)[metric.key] }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <div v-if="stats.posts.length" class="rounded border border-gray-500 p-4">
            <p class="mb-2 text-xs text-gray-400">Latest posts</p>

            <ul class="space-y-1">
                <li
                    v-for="post in stats.posts"
                    :key="post.id"
                    class="flex items-center justify-between text-sm text-gray-300"
                >
                    <span>{{ post.title }}</span>
                    <span class="text-xs text-gray-400">{{ post.creator?.name ?? 'System' }}</span>
                </li>
            </ul>
        </div>
    </div>
</template>