<script setup lang="ts">
import type { Pipeline } from '@/types';

interface Props {
    pipeline: Pipeline;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Basic details</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Title</dt>
                <dd class="text-sm">
                    {{ pipeline.title }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Description</dt>
                <dd class="text-sm">
                    {{ pipeline.description ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Default</dt>
                <dd class="text-sm">
                    {{ pipeline.is_default ? 'Yes' : 'No' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Status</dt>
                <dd class="text-sm">
                    <span
                        v-if="pipeline.status"
                        :style="{
                            backgroundColor:
                                pipeline.status.background_colour ?? '#e2e8f0',
                            color: pipeline.status.text_colour ?? '#1a202c',
                        }"
                        class="rounded px-2 py-0.5 text-xs font-medium"
                    >
                        {{ pipeline.status.title }}
                    </span>
                    <span v-else>-</span>
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Assigned to</dt>
                <dd class="text-sm">
                    {{ pipeline.assignee?.name ?? 'Unassigned' }}
                </dd>
            </div>
        </dl>
    </div>
</template>
