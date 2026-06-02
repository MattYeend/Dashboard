<script setup lang="ts">
import type { TaskStatus } from '@/types'
import TaskStatusRow from './components/TaskStatusRow.vue'

defineProps<{ taskStatuses: TaskStatus[] }>()
</script>

<template>
    <div>
        <div class="flex justify-between items-centre mb-4">
            <h1 class="text-xl font-semibold">Task Statuses</h1>
            <Link :href="route('task-statuses.create')" class="btn btn-primary">
                Add Status
            </Link>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr>
                    <th class="text-left p-2">Title</th>
                    <th class="text-left p-2">Description</th>
                    <th class="text-left p-2">Preview</th>
                    <th class="text-left p-2">Actions</th>
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