<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import TaskAssignmentDetails from '@/pages/Tasks/components/TaskAssignmentDetails.vue'
import TaskBasicDetails from '@/pages/Tasks/components/TaskBasicDetails.vue'
import TaskDateDetails from '@/pages/Tasks/components/TaskDateDetails.vue'
import AuthenticatedLayout from '@/layouts/AuthenticatedLayout.vue'

interface TaskStatus {
    id: number
    title: string
    background_colour: string | null
    text_colour: string | null
}

interface Assignee {
    id: number
    name: string
}

interface Task {
    id: number
    title: string
    description: string | null
    due_date: string | null
    assigned_date: string | null
    assigned_to: number | null
    status_id: number | null
    status: TaskStatus | null
    assignee: Assignee | null
    created_at: string
    updated_at: string
    deleted_at: string | null
    restored_at: string | null
    created_by: number | null
    updated_by: number | null
    deleted_by: number | null
    restored_by: number | null
}

interface PermissionsMeta {
    can_create: boolean
    can_view_any: boolean
}

defineProps<{
    task: Task
    permissions_meta: PermissionsMeta
}>()
</script>

<template>
    <Head :title="`Task - ${task.title}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h4 mb-0">{{ task.title }}</h1>
                <div class="d-flex gap-2">
                    <Link
                        v-if="permissions_meta.can_create"
                        :href="route('tasks.edit', task.id)"
                        class="btn btn-primary btn-sm"
                    >
                        Edit
                    </Link>
                    <Link :href="route('tasks.index')" class="btn btn-secondary btn-sm">
                        Back to Tasks
                    </Link>
                </div>
            </div>
        </template>

        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-lg-8">
                    <TaskBasicDetails :task="task" />
                    <TaskAssignmentDetails :task="task" />
                    <TaskDateDetails :task="task" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>