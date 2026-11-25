<script setup lang="ts">
import { formatCriterionValue, humaniseKey } from '@/lib/strings';
import type { CampaignSegment } from '@/types';

interface Props {
    campaignSegment: CampaignSegment;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Criteria</h2>

        <p
            v-if="!campaignSegment.criteria?.length"
            class="text-sm text-gray-400"
        >
            No criteria set.
        </p>

        <ul v-else class="space-y-2">
            <li
                v-for="(criterion, index) in campaignSegment.criteria"
                :key="index"
                class="flex flex-wrap items-center gap-2 rounded border px-3 py-2 text-sm"
            >
                <span class="font-medium">{{
                    humaniseKey(criterion.field)
                }}</span>
                <span class="text-gray-400">{{
                    humaniseKey(criterion.operator).toLowerCase()
                }}</span>
                <span>{{ formatCriterionValue(criterion.value) }}</span>
            </li>
        </ul>
    </div>
</template>
