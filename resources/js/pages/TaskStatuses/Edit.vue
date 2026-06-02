<script setup lang="ts">
import type { TaskStatus } from '@/types';
import TaskStatusForm from './components/TaskStatusForm.vue';

defineProps<{ taskStatus: TaskStatus }>();
</script>

<template>
    <div>
        <h1 class="mb-4 text-xl font-semibold">Edit Task Status</h1>
        <TaskStatusForm
            :action="route('task-statuses.update', taskStatus.id)"
            method="put"
            :initial="taskStatus"
        />
    </div>
</template>
