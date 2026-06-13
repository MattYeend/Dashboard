<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { update as taskStatusesUpdate } from '@/routes/task-statuses';
import type { TaskStatus } from '@/types';
import TaskStatusForm from './components/TaskStatusForm.vue';
import type { TaskStatusFormData } from './components/TaskStatusForm.vue';

const props = defineProps<{
    taskStatus: TaskStatus;
}>();

const form = useForm<TaskStatusFormData>({
    title: props.taskStatus.title,
    description: props.taskStatus.description,
    background_colour: props.taskStatus.background_colour,
    text_colour: props.taskStatus.text_colour,
});

function onFormUpdate(updated: TaskStatusFormData): void {
    Object.assign(form, updated);
}

function submit(): void {
    form.put(taskStatusesUpdate.url(props.taskStatus.id));
}
</script>

<template>
    <div class="py-6">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h1 class="text-grey-900 mb-6 text-2xl font-semibold">
                Edit Task Status
            </h1>
            <TaskStatusForm
                :form="form"
                :errors="form.errors"
                submit-label="Update Task Status"
                :processing="form.processing"
                @update:form="onFormUpdate"
                @submit="submit"
            />
        </div>
    </div>
</template>