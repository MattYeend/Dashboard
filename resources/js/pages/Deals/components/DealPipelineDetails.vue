<script setup lang="ts">
import type { Deal } from '@/types';

interface Props {
    deal: Deal;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Pipeline details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Pipeline</dt>
                <dd class="text-sm">
                    {{ deal.pipeline?.title ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Stage</dt>
                <dd class="text-sm">
                    <span
                        v-if="deal.stage"
                        :style="{
                            backgroundColor:
                                deal.stage.background_colour ?? '#e2e8f0',
                            color: deal.stage.text_colour ?? '#1a202c',
                        }"
                        class="rounded px-2 py-0.5 text-xs font-medium"
                    >
                        {{ deal.stage.title }}
                    </span>
                    <span v-else>-</span>
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Status</dt>
                <dd class="text-sm">
                    <span
                        v-if="deal.status"
                        :style="{
                            backgroundColor:
                                deal.status.background_colour ?? '#e2e8f0',
                            color: deal.status.text_colour ?? '#1a202c',
                        }"
                        class="rounded px-2 py-0.5 text-xs font-medium"
                    >
                        {{ deal.status.title }}
                    </span>
                    <span v-else>-</span>
                </dd>
            </div>
        </dl>
    </div>
</template>
