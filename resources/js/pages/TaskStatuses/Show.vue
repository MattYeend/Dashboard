<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import {
    edit as taskStatusesEdit,
    destroy as taskStatusesDestroy,
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

function destroy(): void {
    if (!props.taskStatus?.id) {
        return;
    }

    if (confirm('Are you sure you want to delete this task status?')) {
        router.delete(taskStatusesDestroy.url(props.taskStatus.id));
    }
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="items-centre mb-6 flex justify-between">
                <h1 class="text-grey-900 text-2xl font-semibold">
                    {{ taskStatus.title }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="taskStatusesEdit.url(taskStatus.id)"
                        class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        v-if="permissions_meta.can_create"
                        type="button"
                        class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="destroy"
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
    </div>
</template>