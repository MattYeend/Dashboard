<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { store as tasksStore } from '@/routes/tasks';
import type { TaskFormData, TaskStatus, UserOption } from '@/types';
import TaskForm from './components/TaskForm.vue';

defineProps<{
    statuses: TaskStatus[];
    users: UserOption[];
}>();

const form = useForm<TaskFormData>({
    title: '',
    description: null,
    due_date: null,
    assigned_date: null,
    assigned_to: null,
    status_id: null,
});

function onFormUpdate(updated: TaskFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.post(tasksStore.url());
}
</script>

<template>
    <div>
        <h1 class="mb-4 text-xl font-semibold">Create Task</h1>
        <TaskForm
            :form="form"
            :errors="form.errors"
            :statuses="statuses"
            :users="users"
            submit-label="Create Task"
            :processing="form.processing"
            @update:form="onFormUpdate"
            @submit="submit"
        />
    </div>
</template>
