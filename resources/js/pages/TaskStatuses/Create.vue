<script setup lang="ts">
import TaskStatusForm from './components/TaskStatusForm.vue'
</script>

<template>
    <div>
        <h1 class="text-xl font-semibold mb-4">Create Task Status</h1>
        <TaskStatusForm :action="route('task-statuses.store')" method="post" />
    </div>
</template>