<script setup lang="ts">
import type { TaskStatusFormData } from './TaskStatusForm.vue';

interface Errors {
    background_colour?: string;
    text_colour?: string;
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
            <label
                for="background_colour"
                class="text-gray-700 block text-sm font-medium"
            >
                Background Colour
            </label>
            <div class="mt-1 flex items-center gap-3">
                <input
                    :value="form.background_colour"
                    type="color"
                    class="h-10 w-16 cursor-pointer rounded border"
                    @input="
                        update(
                            'background_colour',
                            ($event.target as HTMLInputElement).value,
                        )
                    "
                />
                <input
                    id="background_colour"
                    :value="form.background_colour"
                    type="text"
                    maxlength="7"
                    class="border-gray-300 block w-32 rounded-md font-mono shadow-sm sm:text-sm"
                    placeholder="#ffffff"
                    @input="
                        update(
                            'background_colour',
                            ($event.target as HTMLInputElement).value,
                        )
                    "
                />
            </div>
            <p
                v-if="errors.background_colour"
                class="mt-1 text-sm text-red-600"
            >
                {{ errors.background_colour }}
            </p>
        </div>
        <div>
            <label
                for="text_colour"
                class="text-gray-700 block text-sm font-medium"
            >
                Text Colour
            </label>
            <div class="mt-1 flex items-center gap-3">
                <input
                    :value="form.text_colour"
                    type="color"
                    class="h-10 w-16 cursor-pointer rounded border"
                    @input="
                        update(
                            'text_colour',
                            ($event.target as HTMLInputElement).value,
                        )
                    "
                />
                <input
                    id="text_colour"
                    :value="form.text_colour"
                    type="text"
                    maxlength="7"
                    class="border-gray-300 block w-32 rounded-md font-mono shadow-sm sm:text-sm"
                    placeholder="#000000"
                    @input="
                        update(
                            'text_colour',
                            ($event.target as HTMLInputElement).value,
                        )
                    "
                />
            </div>
            <p v-if="errors.text_colour" class="mt-1 text-sm text-red-600">
                {{ errors.text_colour }}
            </p>
        </div>
        <div>
            <label class="text-gray-700 block text-sm font-medium"
                >Preview</label
            >
            <div class="mt-1">
                <span
                    class="inline-block rounded px-3 py-1 text-sm font-medium"
                    :style="{
                        backgroundColor: form.background_colour,
                        color: form.text_colour,
                    }"
                >
                    {{ form.title || 'Status Label' }}
                </span>
            </div>
        </div>
    </div>
</template>
