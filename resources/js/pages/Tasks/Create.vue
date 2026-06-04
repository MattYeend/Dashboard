<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3'
import TaskForm from '@/pages/Tasks/components/TaskForm.vue'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'

interface TaskStatus {
    id: number
    title: string
    background_colour: string | null
    text_colour: string | null
}

interface UserOption {
    id: number
    name: string
}

defineProps<{
    statuses: TaskStatus[]
    users: UserOption[]
}>()

const form = useForm({
    title: '',
    description: null as string | null,
    due_date: null as string | null,
    assigned_date: null as string | null,
    assigned_to: null as number | null,
    status_id: null as number | null,
})

function submit(): void {
    form.post(route('tasks.store'))
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
                        @submit="submit"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>