<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { index as taskStatusesIndex } from '@/routes/task-statuses';
import TaskStatusBasicDetailsForm from './TaskStatusBasicDetailsForm.vue';
import TaskStatusColourForm from './TaskStatusColourForm.vue';

export interface TaskStatusFormData {
    title: string;
    description: string | null;
    background_colour: string;
    text_colour: string;
}

interface Errors {
    title?: string;
    description?: string;
    background_colour?: string;
    text_colour?: string;
}

defineProps<{
    form: TaskStatusFormData;
    errors: Errors;
    submitLabel: string;
    processing: boolean;
}>();

const emit = defineEmits<{
    (e: 'submit'): void;
    (e: 'update:form', value: TaskStatusFormData): void;
}>();
</script>

<template>
    <form class="space-y-6" @submit.prevent="emit('submit')">
        <TaskStatusBasicDetailsForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />
        <TaskStatusColourForm
            :form="form"
            :errors="errors"
            @update:form="emit('update:form', { ...form, ...$event })"
        />

        <div class="items-center flex justify-end space-x-3">
            <Link
                :href="taskStatusesIndex.url()"
                class="text-gray-700 rounded-md px-4 py-2 text-sm font-medium"
            >
                Cancel
            </Link>
            <button
                type="submit"
                :disabled="processing"
                class="items-center inline-flex rounded-md px-4 py-2 text-sm font-medium text-white disabled:opacity-50"
            >
                {{ submitLabel }}
            </button>
        </div>
    </form>
</template>
