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
    <div>
        <div class="mb-3">
            <label for="title" class="form-label">Title <span class="text-danger">*</span></label>
            <input
                id="title"
                :value="form.title"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': errors.title }"
                placeholder="Enter task title"
                @input="update('title', ($event.target as HTMLInputElement).value)"
            />
            <div v-if="errors.title" class="invalid-feedback">{{ errors.title }}</div>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea
                id="description"
                :value="form.description ?? ''"
                class="form-control"
                :class="{ 'is-invalid': errors.description }"
                rows="4"
                placeholder="Enter task description"
                @input="update('description', ($event.target as HTMLTextAreaElement).value || null)"
            ></textarea>
            <div v-if="errors.description" class="invalid-feedback">{{ errors.description }}</div>
        </div>

        <div class="mb-3">
            <label for="status_id" class="form-label">Status</label>
            <select
                id="status_id"
                :value="form.status_id"
                class="form-select"
                :class="{ 'is-invalid': errors.status_id }"
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
            <div v-if="errors.status_id" class="invalid-feedback">{{ errors.status_id }}</div>
        </div>
    </div>
</template>