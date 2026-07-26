<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PipelineAuditDetails from '@/pages/Pipelines/components/PipelineAuditDetails.vue';
import PipelineBasicDetails from '@/pages/Pipelines/components/PipelineBasicDetails.vue';
import type { Pipeline } from '@/types';
import {
    edit as pipelinesEdit,
    destroy as pipelinesDestroy,
    index as pipelinesIndex,
} from '@/routes/pipelines';

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
