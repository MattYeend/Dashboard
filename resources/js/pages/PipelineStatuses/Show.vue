<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import {
    edit as pipelineStatusesEdit,
    destroy as pipelineStatusesDestroy,
    index as pipelineStatusesIndex,
} from '@/routes/pipeline-statuses';
import type { PipelineStatus, PermissionsMeta } from '@/types';
import PipelineStatusAuditDetails from './components/PipelineStatusAuditDetails.vue';
import PipelineStatusBasicDetails from './components/PipelineStatusBasicDetails.vue';
import PipelineStatusColourDetails from './components/PipelineStatusColourDetails.vue';

interface Props {
    pipelineStatus: PipelineStatus;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.pipelineStatus?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.pipelineStatus?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(pipelineStatusesDestroy.url(props.pipelineStatus.id), {
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
                    {{ pipelineStatus.title }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="pipelineStatusesIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="pipelineStatusesEdit.url(pipelineStatus.id)"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="permissions_meta.can_create"
                        type="button"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="requestDestroy"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <PipelineStatusBasicDetails :pipeline-status="pipelineStatus" />
                <PipelineStatusColourDetails
                    :pipeline-status="pipelineStatus"
                />
                <PipelineStatusAuditDetails :pipeline-status="pipelineStatus" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete pipeline status"
            description="This pipeline status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
