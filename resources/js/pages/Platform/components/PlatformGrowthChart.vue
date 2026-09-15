<script setup lang="ts">
import { computed } from 'vue';
import type { PlatformReportingMetrics } from '@/types';

const props = defineProps<{
    signupsOverTime: PlatformReportingMetrics['signups_over_time'];
}>();

const chartWidth = 600;
const chartHeight = 200;
const barGap = 4;

const entries = computed(() => Object.entries(props.signupsOverTime));

const maxValue = computed(() =>
    Math.max(1, ...entries.value.map(([, total]) => total)),
);

const barWidth = computed(() => {
    if (entries.value.length === 0) {
        return 0;
    }

    return chartWidth / entries.value.length - barGap;
});

const bars = computed(() =>
    entries.value.map(([period, total], index) => {
        const height = (total / maxValue.value) * chartHeight;

        return {
            period,
            total,
            x: index * (barWidth.value + barGap),
            y: chartHeight - height,
            height,
        };
    }),
);
</script>

<template>
    <div class="rounded-md border border-gray-500 p-4">
        <h3 class="mb-3 text-xs text-gray-400">Signups over time</h3>

        <svg
            :viewBox="`0 0 ${chartWidth} ${chartHeight}`"
            class="w-full"
            role="img"
            aria-label="Signups over time"
        >
            <line
                x1="0"
                :y1="chartHeight"
                :x2="chartWidth"
                :y2="chartHeight"
                class="stroke-gray-500"
                stroke-width="1"
            />

            <g v-for="bar in bars" :key="bar.period">
                <rect
                    :x="bar.x"
                    :y="bar.y"
                    :width="barWidth"
                    :height="bar.height"
                    class="fill-gray-300"
                />
                <title>{{ bar.period }}: {{ bar.total }}</title>
            </g>
        </svg>

        <div class="mt-2 flex justify-between text-xs text-gray-400">
            <span v-if="bars.length">{{ bars[0].period }}</span>
            <span v-if="bars.length > 1">
                {{ bars[bars.length - 1].period }}
            </span>
        </div>
    </div>
</template>
