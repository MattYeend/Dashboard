<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PipelineStageAppearanceDetails from '@/pages/PipelineStages/components/PipelineStageAppearanceDetails.vue';
import PipelineStageAuditDetails from '@/pages/PipelineStages/components/PipelineStageAuditDetails.vue';
import PipelineStageBasicDetails from '@/pages/PipelineStages/components/PipelineStageBasicDetails.vue';
import PipelineStageOutcomeDetails from '@/pages/PipelineStages/components/PipelineStageOutcomeDetails.vue';
import {
    edit as pipelineStagesEdit,
    destroy as pipelineStagesDestroy,
    index as pipelineStagesIndex,
} from '@/routes/pipelines/stages';
import type { Pipeline, PipelineStage, PermissionsMeta } from '@/types';

interface Props {
    pipeline: Pipeline;
    pipeline_stage: PipelineStage;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.pipeline_stage?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.pipeline_stage?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(
        pipelineStagesDestroy.url({
            pipeline: props.pipeline.id,
            stage: props.pipeline_stage.id,
        }),
        {
            onFinish: () => {
                deleteProcessing.value = false;
                deleteDialogOpen.value = false;
            },
        },
    );
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mb-6 flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-gray-300">
                    {{ pipeline_stage.title }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="
                            pipelineStagesIndex.url({ pipeline: pipeline.id })
                        "
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="
                            pipelineStagesEdit.url({
                                pipeline: pipeline.id,
                                stage: pipeline_stage.id,
                            })
                        "
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
                <PipelineStageBasicDetails :stage="pipeline_stage" />
                <PipelineStageAppearanceDetails :stage="pipeline_stage" />
                <PipelineStageOutcomeDetails :stage="pipeline_stage" />
                <PipelineStageAuditDetails :stage="pipeline_stage" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete pipeline stage"
            description="This pipeline stage will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
