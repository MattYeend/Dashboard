<script setup lang="ts">
import { computed } from 'vue';
import type { Deal } from '@/types';

const props = defineProps<{
    deal: Deal;
}>();

const formattedValue = computed(() =>
    new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: props.deal.currency ?? 'GBP',
    }).format((props.deal.value ?? 0) / 100),
);
</script>

<template>
    <div
        class="cursor-grab rounded border border-gray-500 p-2 active:cursor-grabbing"
    >
        <p class="text-sm font-medium text-gray-300">
            {{ deal.title }}
        </p>
        <p v-if="deal.company" class="text-xs text-gray-400">
            {{ deal.company.name }}
        </p>
        <div class="mt-1 flex items-center justify-between">
            <span class="text-xs text-gray-300">{{ formattedValue }}</span>
            <span
                v-if="deal.probability !== null"
                class="text-xs text-gray-400"
            >
                {{ deal.probability }}%
            </span>
        </div>
    </div>
</template>
