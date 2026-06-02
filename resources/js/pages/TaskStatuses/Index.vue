<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { TaskStatus } from '@/types';
import TaskStatusRow from './components/TaskStatusRow.vue';

defineProps<{ taskStatuses: TaskStatus[] }>();
</script>

<template>
    <div>
        <div class="mb-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold">Task Statuses</h1>
            <Link :href="route('task-statuses.create')" class="btn btn-primary">
                Add Status
            </Link>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr>
                    <th class="p-2 text-left">Title</th>
                    <th class="p-2 text-left">Description</th>
                    <th class="p-2 text-left">Preview</th>
                    <th class="p-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>
                <TaskStatusRow
                    v-for="status in taskStatuses"
                    :key="status.id"
                    :status="status"
                />
            </tbody>
        </table>
    </div>
</template>
