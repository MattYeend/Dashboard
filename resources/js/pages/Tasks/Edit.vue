<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { update as tasksUpdate } from '@/routes/tasks';
import type { Task, TaskStatus, UserOption } from '@/types';
import TaskForm from './components/TaskForm.vue';

interface Props {
    task: Task;
    statuses: TaskStatus[];
    users: UserOption[];
}

const props = defineProps<Props>();

const form = useForm({
    title: props.task.title,
    description: props.task.description,
    due_date: props.task.due_date,
    assigned_date: props.task.assigned_date,
    assigned_to: props.task.assigned_to,
    status_id: props.task.status_id,
});

function submit(): void {
    form.put(tasksUpdate.url(props.task.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-grey-900 mb-6 text-2xl font-semibold">Edit Task</h1>
            <TaskForm
                v-model:title="form.title"
                v-model:description="form.description"
                v-model:due-date="form.due_date"
                v-model:assigned-date="form.assigned_date"
                v-model:assigned-to="form.assigned_to"
                v-model:status-id="form.status_id"
                :is-editing="true"
                :processing="form.processing"
                :statuses="statuses"
                :users="users"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
