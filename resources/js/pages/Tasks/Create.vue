<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { store as tasksStore } from '@/routes/tasks';
import type { TaskStatus, UserOption } from '@/types';
import TaskForm from './components/TaskForm.vue';

defineProps<{
    statuses: TaskStatus[];
    users: UserOption[];
}>();

const form = useForm({
    title: '',
    description: null,
    due_date: null,
    assigned_date: null,
    assigned_to: null,
    status_id: null,
});

function submit(): void {
    form.post(tasksStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="mb-6 text-2xl font-semibold text-gray-300">
                Create Task
            </h1>
            <TaskForm
                v-model:title="form.title"
                v-model:description="form.description"
                v-model:due-date="form.due_date"
                v-model:assigned-date="form.assigned_date"
                v-model:assigned-to="form.assigned_to"
                v-model:status-id="form.status_id"
                :is-editing="false"
                :processing="form.processing"
                :statuses="statuses"
                :users="users"
                :errors="form.errors"
                @submit="submit"
            />
        </div>
    </div>
</template>
