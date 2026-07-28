<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PipelineAuditDetails from '@/pages/Pipelines/components/PipelineAuditDetails.vue';
import PipelineBasicDetails from '@/pages/Pipelines/components/PipelineBasicDetails.vue';
import {
    edit as pipelinesEdit,
    destroy as pipelinesDestroy,
    index as pipelinesIndex,
} from '@/routes/pipelines';
import {
    index as pipelineStagesIndex,
    create as pipelineStagesCreate,
} from '@/routes/pipelines/stages';
import type { Pipeline } from '@/types';

interface Props {
    pipeline: Pipeline;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    deleteDialogOpen.value = true;
}

function destroy(): void {
    deleteProcessing.value = true;

    router.delete(pipelinesDestroy.url(props.pipeline.id), {
        onFinish: () => {
            deleteProcessing.value = false;
            deleteDialogOpen.value = false;
        },
    });
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ pipeline.title }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="pipelinesIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="pipelinesEdit.url(props.pipeline.id)"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="requestDestroy"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <PipelineBasicDetails :pipeline="pipeline" />

                <div class="overflow-hidden shadow sm:rounded-lg">
                    <div
                        class="flex items-center justify-between px-4 py-5 sm:px-6"
                    >
                        <h3 class="text-lg leading-6 font-medium text-gray-300">
                            Stages
                        </h3>
                        <div class="space-x-2">
                            <Link
                                :href="
                                    pipelineStagesCreate.url({
                                        pipeline: pipeline.id,
                                    })
                                "
                                class="text-sm text-gray-400 hover:text-gray-300"
                            >
                                Add stage
                            </Link>
                            <Link
                                :href="
                                    pipelineStagesIndex.url({
                                        pipeline: pipeline.id,
                                    })
                                "
                                class="text-sm text-gray-400 hover:text-gray-300"
                            >
                                Manage stages
                            </Link>
                        </div>
                    </div>
                    <div class="border-t border-gray-500">
                        <ul
                            v-if="pipeline.stages?.length"
                            class="divide-y divide-gray-500"
                        >
                            <li
                                v-for="stage in pipeline.stages"
                                :key="stage.id"
                                class="flex items-center justify-between px-4 py-3 sm:px-6"
                            >
                                <span
                                    class="inline-block rounded px-2 py-1 text-sm font-medium"
                                    :style="{
                                        backgroundColor:
                                            stage.background_colour,
                                        color: stage.text_colour,
                                    }"
                                >
                                    {{ stage.title }}
                                </span>
                                <span class="text-sm text-gray-400">
                                    <template v-if="stage.is_won">Won</template>
                                    <template v-else-if="stage.is_lost"
                                        >Lost</template
                                    >
                                    <template v-else>Open</template>
                                </span>
                            </li>
                        </ul>
                        <p
                            v-else
                            class="px-4 py-5 text-sm text-gray-400 sm:px-6"
                        >
                            No stages have been added to this pipeline yet.
                        </p>
                    </div>
                </div>

                <PipelineAuditDetails :pipeline="pipeline" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete pipeline"
            description="This pipeline will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
