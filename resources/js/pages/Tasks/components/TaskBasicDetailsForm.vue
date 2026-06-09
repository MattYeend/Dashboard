<script setup lang="ts">
import type { TaskFormData, TaskStatus } from '@/types';

interface Errors {
    title?: string;
    description?: string;
    status_id?: string;
}

const props = defineProps<{
    form: TaskFormData;
    errors: Errors;
    statuses: TaskStatus[];
}>();

const emit = defineEmits<{
    (e: 'update:form', value: TaskFormData): void;
}>();

function update<K extends keyof TaskFormData>(field: K, value: TaskFormData[K]): void {
    emit('update:form', { ...props.form, [field]: value });
}
</script>

<template>
    <div class="space-y-4">
        <div>
            <label for="title" class="text-grey-700 block text-sm font-medium">
                Title <span class="text-red-600">*</span>
            </label>
            <input
                id="title"
                :value="form.title"
                type="text"
                class="border-grey-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                placeholder="Enter task title"
                @input="update('title', ($event.target as HTMLInputElement).value)"
            />
            <p v-if="errors.title" class="mt-1 text-sm text-red-600">
                {{ errors.title }}
            </p>
        </div>

        <div>
            <label for="description" class="text-grey-700 block text-sm font-medium">
                Description
            </label>
            <textarea
                id="description"
                :value="form.description ?? ''"
                class="border-grey-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                rows="4"
                placeholder="Enter task description"
                @input="update('description', ($event.target as HTMLTextAreaElement).value || null)"
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">
                {{ errors.description }}
            </p>
        </div>

        <div>
            <label for="status_id" class="text-grey-700 block text-sm font-medium">
                Status
            </label>
            <select
                id="status_id"
                :value="form.status_id"
                class="border-grey-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                @change="update('status_id', ($event.target as HTMLSelectElement).value ? Number(($event.target as HTMLSelectElement).value) : null)"
            >
                <option :value="null">-- Select a status --</option>
                <option
                    v-for="status in statuses"
                    :key="status.id"
                    :value="status.id"
                >
                    {{ status.title }}
                </option>
            </select>
            <p v-if="errors.status_id" class="mt-1 text-sm text-red-600">
                {{ errors.status_id }}
            </p>
        </div>
    </div>
</template>