<script setup lang="ts">
import { buildDashboardStatGroups } from './dashboardStatGroups';
import StatCard from './StatCard.vue';
import type { DashboardStats } from '@/types';

const props = defineProps<{
    stats: DashboardStats;
}>();

const groups = buildDashboardStatGroups(props.stats);
</script>

<template>
    <div class="space-y-6">
        <div
            v-for="group in groups"
            :key="group.label"
            class="rounded border border-gray-500 p-4"
        >
            <p class="mb-4 text-xs text-gray-400">
                {{ group.label }}
            </p>

            <div class="grid grid-cols-3 gap-4">
                <StatCard
                    v-for="metric in group.metrics"
                    :key="metric.key"
                    :label="metric.label"
                    :value="group.values[metric.key]"
                />
            </div>
        </div>
    </div>
</template>
