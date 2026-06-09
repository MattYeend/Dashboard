<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import TaskAssignmentDetailsForm from '@/pages/Tasks/components/TaskAssignmentDetailsForm.vue';
import TaskBasicDetailsForm from '@/pages/Tasks/components/TaskBasicDetailsForm.vue';
import TaskDateDetailsForm from '@/pages/Tasks/components/TaskDateDetailsForm.vue';
import { index as tasksIndex } from '@/routes/tasks';
import type { TaskFormData, TaskStatus, UserOption } from '@/types';

interface Errors {
    title?: string;
    description?: string;
    due_date?: string;
    assigned_date?: string;
    assigned_to?: string;
    status_id?: string;
}

defineProps<{
    form: TaskFormData;
    errors: Errors;
    statuses: TaskStatus[];
    users: UserOption[];
    submitLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'update:form', value: TaskFormData): void;
}>();
</script>

<template>
    <form @submit.prevent="emit('submit')">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Basic Details</h5>
            </div>
            <div class="card-body">
                <TaskBasicDetailsForm
                    :form="form"
                    :errors="errors"
                    :statuses="statuses"
                    @update:form="emit('update:form', $event)"
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
                    @update:form="emit('update:form', $event)"
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
                    @update:form="emit('update:form', $event)"
                />
            </div>
        </div>

        <div class="d-flex gap-2">
            <button
                type="submit"
                class="btn btn-primary"
                :disabled="processing"
            >
                {{ submitLabel }}
            </button>
            <Link :href="tasksIndex.url()" class="btn btn-secondary">
                Cancel
            </Link>
        </div>
    </form>
</template>
