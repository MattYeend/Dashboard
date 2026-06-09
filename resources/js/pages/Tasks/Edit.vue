<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { update as tasksUpdate } from '@/routes/tasks';
import type { Task, TaskFormData, TaskStatus, UserOption } from '@/types';
import TaskForm from './components/TaskForm.vue';

const props = defineProps<{
    task: Task;
    statuses: TaskStatus[];
    users: UserOption[];
}>();

const form = useForm<TaskFormData>({
    title: props.task.title,
    description: props.task.description,
    due_date: props.task.due_date,
    assigned_date: props.task.assigned_date,
    assigned_to: props.task.assigned_to,
    status_id: props.task.status_id,
});

function onFormUpdate(updated: TaskFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.put(tasksUpdate.url(props.task.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-grey-900 mb-6 text-2xl font-semibold">
                Edit Task
            </h1>
            <TaskForm
                :form="form"
                :errors="form.errors"
                :statuses="statuses"
                :users="users"
                submit-label="Update Task"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
