<script setup lang="ts">
import type { PipelineStage } from '@/types';

interface Props {
    stage: PipelineStage;
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
                    {{ stage.title }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Description</dt>
                <dd class="text-sm">
                    {{ stage.description ?? '-' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Position</dt>
                <dd class="text-sm">
                    {{ stage.position }}
                </dd>
            </div>
        </dl>
    </div>
</template>
