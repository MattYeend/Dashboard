<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import TaskForm from '@/pages/Tasks/components/TaskForm.vue';
import type { Task, TaskFormData, TaskStatus, UserOption } from '@/types';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

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
    form.put(route('tasks.update', props.task.id));
}
</script>

<template>
    <Head :title="`Edit Task – ${task.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h4 mb-0">Edit Task</h1>
                <div class="d-flex gap-2">
                    <Link
                        :href="route('tasks.show', task.id)"
                        class="btn btn-secondary btn-sm"
                    >
                        View Task
                    </Link>
                    <Link
                        :href="route('tasks.index')"
                        class="btn btn-secondary btn-sm"
                    >
                        Back to Tasks
                    </Link>
                </div>
            </div>
        </template>

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-lg-8">
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
        </div>
    </AuthenticatedLayout>
</template>
