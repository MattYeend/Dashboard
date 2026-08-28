<script setup lang="ts">
import type { DashboardChart } from '@/types';

interface Props {
    charts: DashboardChart[];
}

const props = defineProps<Props>();
</script>

<template>
    <div class="space-y-6">
        <div
            v-for="chart in props.charts"
            :key="chart.key"
            class="rounded border border-gray-500 p-4"
        >
            <p class="mb-4 text-xs text-gray-400">
                {{ chart.label }}
            </p>

            <div class="grid grid-cols-3 gap-4">
                <div v-for="item in chart.data" :key="item.label">
                    <p class="text-xs text-gray-400">
                        {{ item.label }}
                    </p>

                    <p class="text-lg font-semibold text-gray-300">
                        {{ item.value }}
                    </p>
                </div>
            </div>
        </div>

        <p v-if="props.charts.length === 0" class="text-sm text-gray-400">
            No charts configured yet.
        </p>
    </div>
</template>
