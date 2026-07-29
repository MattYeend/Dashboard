<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    index as pipelineStagesIndex,
    create as pipelineStagesCreate,
} from '@/routes/pipelines/stages';
import type { Pipeline } from '@/types';

interface Props {
    pipeline: Pipeline;
}

defineProps<Props>();
</script>

<template>
    <div class="overflow-hidden shadow sm:rounded-lg">
        <div class="flex items-center justify-between px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-300">Stages</h3>
            <div class="space-x-2">
                <Link
                    :href="pipelineStagesCreate.url({ pipeline: pipeline.id })"
                    class="text-sm text-gray-400 hover:text-gray-300"
                >
                    Add stage
                </Link>
                <Link
                    :href="pipelineStagesIndex.url({ pipeline: pipeline.id })"
                    class="text-sm text-gray-400 hover:text-gray-300"
                >
                    Manage stages
                </Link>
            </div>
        </div>
        <div class="border-t border-gray-500">
            <ul v-if="pipeline.stages?.length" class="divide-y divide-gray-500">
                <li
                    v-for="stage in pipeline.stages"
                    :key="stage.id"
                    class="flex items-center justify-between px-4 py-3 sm:px-6"
                >
                    <span
                        class="inline-block rounded px-2 py-1 text-sm font-medium"
                        :style="{
                            backgroundColor: stage.background_colour,
                            color: stage.text_colour,
                        }"
                    >
                        {{ stage.title }}
                    </span>
                    <span class="text-sm text-gray-400">
                        <template v-if="stage.is_won">Won</template>
                        <template v-else-if="stage.is_lost">Lost</template>
                        <template v-else>Open</template>
                    </span>
                </li>
            </ul>
            <p v-else class="px-4 py-5 text-sm text-gray-400 sm:px-6">
                No stages have been added to this pipeline yet.
            </p>
        </div>
    </div>
</template>
