<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import TaskAssignmentDetails from '@/pages/Tasks/components/TaskAssignmentDetails.vue';
import TaskBasicDetails from '@/pages/Tasks/components/TaskBasicDetails.vue';
import TaskDateDetails from '@/pages/Tasks/components/TaskDateDetails.vue';
import {
    edit as tasksEdit,
    destroy as tasksDestroy,
    index as tasksIndex,
} from '@/routes/tasks';
import type { Task, PermissionsMeta } from '@/types';

interface Props {
    task: Task;
    permissions_meta: PermissionsMeta;
}

const props = defineProps<Props>();

function destroy(): void {
    if (!props.task?.id) {
        return;
    }

    if (confirm('Are you sure you want to delete this task?')) {
        router.delete(tasksDestroy.url(props.task.id));
    }
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="items-centre mb-6 flex justify-between">
                <h1 class="text-gray-900 text-2xl font-semibold">
                    {{ task.title }}
                </h1>
                <div class="space-x-2">
                    <Link
                        :href="tasksIndex.url()"
                        class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Back
                    </Link>
                    <Link
                        :href="tasksEdit.url(task.id)"
                        class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium"
                    >
                        Edit
                    </Link>
                    <button
                        type="button"
                        class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium text-red-600"
                        @click="destroy"
                    >
                        Delete
                    </button>
                </div>
            </div>

            <div class="space-y-6">
                <TaskBasicDetails :task="task" />
                <TaskAssignmentDetails :task="task" />
                <TaskDateDetails :task="task" />
            </div>
        </div>
    </div>
</template>
