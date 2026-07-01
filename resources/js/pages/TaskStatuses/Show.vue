<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import {
    edit as taskStatusesEdit,
    destroy as taskStatusesDestroy,
    index as taskStatusIndex,
} from '@/routes/task-statuses';
import type { TaskStatus, PermissionsMeta } from '@/types';
import TaskStatusAuditDetails from './components/TaskStatusAuditDetails.vue';
import TaskStatusBasicDetails from './components/TaskStatusBasicDetails.vue';
import TaskStatusColourDetails from './components/TaskStatusColourDetails.vue';

interface Props {
    taskStatus: TaskStatus;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

const deleteDialogOpen = ref(false);
const deleteProcessing = ref(false);

function requestDestroy(): void {
    if (!props.taskStatus?.id) {
        return;
    }

    deleteDialogOpen.value = true;
}

function destroy(): void {
    if (!props.taskStatus?.id) {
        return;
    }

    deleteProcessing.value = true;

    router.delete(taskStatusesDestroy.url(props.taskStatus.id), {
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
                    {{ taskStatus.title }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="taskStatusIndex.url()"
                        class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="taskStatusesEdit.url(taskStatus.id)"
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
                <TaskStatusBasicDetails :task-status="taskStatus" />
                <TaskStatusColourDetails :task-status="taskStatus" />
                <TaskStatusAuditDetails :task-status="taskStatus" />
            </div>
        </div>

        <ConfirmDialog
            v-model:open="deleteDialogOpen"
            title="Delete task status"
            description="This task status will be moved to trash."
            confirm-label="Delete"
            :processing="deleteProcessing"
            @confirm="destroy"
        />
    </div>
</template>
