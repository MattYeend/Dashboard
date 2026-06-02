<script setup lang="ts">
import type { TaskStatus } from '@/types';
import TaskStatusAuditDetails from './components/TaskStatusAuditDetails.vue';
import TaskStatusBasicDetails from './components/TaskStatusBasicDetails.vue';
import TaskStatusColourDetails from './components/TaskStatusColourDetails.vue';
import TaskStatusMetaDetails from './components/TaskStatusMetaDetails.vue';

defineProps<{ taskStatus: TaskStatus }>();
</script>

<template>
    <div class="space-y-6">
        <div class="items-centre flex justify-between">
            <h1 class="text-xl font-semibold">{{ taskStatus.title }}</h1>
            <div class="flex gap-2">
                <Link :href="route('task-statuses.edit', taskStatus.id)"
                    >Edit</Link
                >
                <Link :href="route('task-statuses.index')">Back</Link>
            </div>
        </div>

        <TaskStatusBasicDetails :task-status="taskStatus" />
        <TaskStatusColourDetails :task-status="taskStatus" />
        <TaskStatusMetaDetails :task-status="taskStatus" />
        <TaskStatusAuditDetails :task-status="taskStatus" />
    </div>
</template>
