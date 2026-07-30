<script setup lang="ts">
import type { Deal } from '@/types';

interface Props {
    deal: Deal;
}

defineProps<Props>();

function formatDate(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Date(value).toLocaleDateString('en-GB');
}
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Value details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Value</dt>
                <dd class="text-sm">{{ deal.currency }} {{ deal.value }}</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Probability</dt>
                <dd class="text-sm">{{ deal.probability }}%</dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Expected close date</dt>
                <dd class="text-sm">
                    {{ formatDate(deal.expected_close_date) }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Closed date</dt>
                <dd class="text-sm">
                    {{ formatDate(deal.closed_at) }}
                </dd>
            </div>
        </dl>
    </div>
</template>
