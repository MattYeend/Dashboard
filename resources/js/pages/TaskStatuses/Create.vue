<script setup lang="ts">
import TaskStatusForm from './components/TaskStatusForm.vue';
</script>

<template>
    <div>
        <h1 class="mb-4 text-xl font-semibold">Create Task Status</h1>
        <TaskStatusForm :action="route('task-statuses.store')" method="post" />
    </div>
</template>
