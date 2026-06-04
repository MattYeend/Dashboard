<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Task, PermissionsMeta } from '@/types';
import TaskAssignmentDetails from './components/TaskAssignmentDetails.vue';
import TaskBasicDetails from './components/TaskBasicDetails.vue';
import TaskDateDetails from './components/TaskDateDetails.vue';

defineProps<{
    task: Task;
    permissions_meta: PermissionsMeta;
}>();
</script>

<template>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold">{{ task.title }}</h1>
            <div class="flex gap-2">
                <Link
                    v-if="permissions_meta.can_create"
                    :href="route('tasks.edit', task.id)"
                >
                    Edit
                </Link>
                <Link :href="route('tasks.index')">Back</Link>
            </div>
        </div>

        <TaskBasicDetails :task="task" />
        <TaskAssignmentDetails :task="task" />
        <TaskDateDetails :task="task" />
    </div>
</template>
