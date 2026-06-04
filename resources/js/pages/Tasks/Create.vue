<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import TaskForm from '@/pages/Tasks/components/TaskForm.vue';
import type { TaskFormData, TaskStatus, UserOption } from '@/types';
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue';

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
    form.post(route('tasks.store'));
}
</script>

<template>
    <Head title="Create Task" />

    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h4 mb-0">Create Task</h1>
                <Link :href="route('tasks.index')" class="btn btn-secondary btn-sm">
                    Back to Tasks
                </Link>
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
                        submit-label="Create Task"
                        :processing="form.processing"
                        @update:form="onFormUpdate"
                        @submit="submit"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>