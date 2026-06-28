<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { InertiaFormProps } from '@inertiajs/vue3';
import TaskAssignmentDetailsForm from '@/pages/Tasks/components/TaskAssignmentDetailsForm.vue';
import TaskBasicDetailsForm from '@/pages/Tasks/components/TaskBasicDetailsForm.vue';
import TaskDateDetailsForm from '@/pages/Tasks/components/TaskDateDetailsForm.vue';
import { index as tasksIndex } from '@/routes/tasks';
import type { TaskStatus, UserOption } from '@/types';

interface TaskFormData {
    title: string;
    description: string | null;
    due_date: string | null;
    assigned_date: string | null;
    assigned_to: number | null;
    status_id: number | null;
}

interface Props {
    isEditing: boolean;
    processing: boolean;
    statuses: TaskStatus[];
    users: UserOption[];
    errors: Partial<InertiaFormProps<TaskFormData>['errors']>;
}

defineProps<Props>();
defineEmits<{ submit: [] }>();

const title = defineModel<string>('title', { required: true });
const description = defineModel<string | null>('description', {
    default: null,
});
const dueDate = defineModel<string | null>('dueDate', { default: null });
const assignedDate = defineModel<string | null>('assignedDate', {
    default: null,
});
const assignedTo = defineModel<number | null>('assignedTo', { default: null });
const statusId = defineModel<number | null>('statusId', { default: null });
</script>

<template>
    <form class="space-y-6" @submit.prevent="$emit('submit')">
        <TaskBasicDetailsForm
            v-model:title="title"
            v-model:description="description"
            v-model:status-id="statusId"
            :statuses="statuses"
            :errors="errors"
        />
        <TaskAssignmentDetailsForm
            v-model:assigned-to="assignedTo"
            :users="users"
            :errors="errors"
        />
        <TaskDateDetailsForm
            v-model:due-date="dueDate"
            v-model:assigned-date="assignedDate"
            :errors="errors"
        />

        <div class="flex items-center justify-end space-x-3">
            <Link
                :href="tasksIndex.url()"
                class="rounded-md px-4 py-2 text-sm font-medium text-gray-400"
            >
                Cancel
            </Link>
            <button
                type="submit"
                :disabled="processing"
                class="inline-flex items-center rounded-md px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
            >
                {{ isEditing ? 'Update Task' : 'Create Task' }}
            </button>
        </div>
    </form>
</template>
