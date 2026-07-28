<script setup lang="ts">
import type { PipelineStage } from '@/types';

interface Props {
    stage: PipelineStage;
}

defineProps<Props>();
</script>

<template>
    <div class="rounded-lg border p-4">
        <h2 class="mb-4 text-sm font-medium text-gray-400">Outcome</h2>

        <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <dt class="text-xs text-gray-400">Won</dt>
                <dd class="text-sm">
                    {{ stage.is_won ? 'Yes' : 'No' }}
                </dd>
            </div>
            <div>
                <dt class="text-xs text-gray-400">Lost</dt>
                <dd class="text-sm">
                    {{ stage.is_lost ? 'Yes' : 'No' }}
                </dd>
            </div>
        </dl>
    </div>
</template>
