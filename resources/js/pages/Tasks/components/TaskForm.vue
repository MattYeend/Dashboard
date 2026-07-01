<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { InertiaFormProps } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
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
            <Link :href="tasksIndex.url()" class="text-sm font-medium">
                Cancel
            </Link>
            <Button type="submit" :disabled="processing">
                {{ isEditing ? 'Update Task' : 'Create Task' }}
            </Button>
        </div>
    </form>
</template>
