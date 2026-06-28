<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { store as taskStatusesStore } from '@/routes/task-statuses';
import TaskStatusForm from './components/TaskStatusForm.vue';
import type { TaskStatusFormData } from './components/TaskStatusForm.vue';

const form = useForm<TaskStatusFormData>({
    title: '',
    description: null,
    background_colour: '#ffffff',
    text_colour: '#000000',
});

function onFormUpdate(updated: TaskStatusFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.post(taskStatusesStore.url());
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-gray-300 mb-6 text-2xl font-semibold">
                Create Task Status
            </h1>
            <TaskStatusForm
                :form="form"
                :errors="form.errors"
                submit-label="Create Task Status"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>
