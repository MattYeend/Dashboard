<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import TaskBasicDetailsForm from '@/Pages/Tasks/components/TaskBasicDetailsForm.vue'
import TaskAssignmentDetailsForm from '@/Pages/Tasks/components/TaskAssignmentDetailsForm.vue'
import TaskDateDetailsForm from '@/Pages/Tasks/components/TaskDateDetailsForm.vue'

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

interface Form {
    title: string
    description: string | null
    due_date: string | null
    assigned_date: string | null
    assigned_to: number | null
    status_id: number | null
}

interface Errors {
    title?: string
    description?: string
    due_date?: string
    assigned_date?: string
    assigned_to?: string
    status_id?: string
}

defineProps<{
    form: Form
    errors: Errors
    statuses: TaskStatus[]
    users: UserOption[]
    submitLabel: string
    processing: boolean
}>()

defineEmits<{
    (e: 'submit'): void
}>()
</script>

<template>
    <form @submit.prevent="$emit('submit')">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Basic Details</h5>
            </div>
            <div class="card-body">
                <TaskBasicDetailsForm
                    :form="form"
                    :errors="errors"
                    :statuses="statuses"
                />
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Assignment</h5>
            </div>
            <div class="card-body">
                <TaskAssignmentDetailsForm
                    :form="form"
                    :errors="errors"
                    :users="users"
                />
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Dates</h5>
            </div>
            <div class="card-body">
                <TaskDateDetailsForm
                    :form="form"
                    :errors="errors"
                />
            </div>
        </div>

        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary" :disabled="processing">
                {{ submitLabel }}
            </button>
            <Link :href="route('tasks.index')" class="btn btn-secondary">
                Cancel
            </Link>
        </div>
    </form>
</template>