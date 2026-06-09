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
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <TaskBasicDetailsForm
            :form="form"
            :errors="errors"
            :statuses="statuses"
            @update:form="emit('update:form', $event)"
        />
        <TaskAssignmentDetailsForm
            :form="form"
            :errors="errors"
            :users="users"
            @update:form="emit('update:form', $event)"
        />
        <TaskDateDetailsForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', $event)"
        />

        <div class="items-centre flex justify-end space-x-3">
            <Link
                :href="tasksIndex.url()"
                class="text-grey-700 rounded-md px-4 py-2 text-sm font-medium"
            >
                Cancel
            </Link>
            <button
                type="submit"
                :disabled="processing"
                class="items-centre inline-flex rounded-md px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
            >
                {{ submitLabel }}
            </button>
        </div>
    </form>
</template>