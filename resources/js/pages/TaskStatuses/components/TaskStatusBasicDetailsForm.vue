<script setup lang="ts">
import type { TaskStatusFormData } from './TaskStatusForm.vue';

interface Errors {
    title?: string;
    description?: string;
}

const props = defineProps<{
    form: TaskStatusFormData;
    errors: Errors;
}>();

const emit = defineEmits<{
    (e: 'update:form', value: TaskStatusFormData): void;
}>();

function update<K extends keyof TaskStatusFormData>(
    field: K,
    value: TaskStatusFormData[K],
): void {
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
                placeholder="Enter status title"
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
                rows="3"
                class="border-grey-300 mt-1 block w-full rounded-md shadow-sm sm:text-sm"
                placeholder="Enter status description"
                @input="update('description', ($event.target as HTMLTextAreaElement).value || null)"
            />
            <p v-if="errors.description" class="mt-1 text-sm text-red-600">
                {{ errors.description }}
            </p>
        </div>
    </div>
</template>