<script setup lang="ts">
import { computed } from 'vue';

interface Props {
    title: string;
    description: string;
    expectedValue: string;
    modelValue: string;
    processing?: boolean;
    error?: string;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
    confirm: [];
    cancel: [];
}>();

const matches = computed(() => props.modelValue === props.expectedValue);

function onInput(event: Event): void {
    emit('update:modelValue', (event.target as HTMLInputElement).value);
}
</script>

<template>
    <div class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="border-gray-500 w-full max-w-md rounded border p-4">
            <h3 class="text-sm font-semibold text-gray-200">{{ title }}</h3>
            <p class="mt-1 text-xs text-gray-400">{{ description }}</p>

            <input
                :value="modelValue"
                type="text"
                class="border-gray-500 mt-3 w-full rounded border px-2 py-1 text-sm text-gray-200"
                @input="onInput"
            />
            <p v-if="error" class="mt-1 text-xs text-red-400">{{ error }}</p>

            <div class="mt-4 flex justify-end gap-2">
                <button type="button" class="text-xs text-gray-400" @click="emit('cancel')">
                    Cancel
                </button>
                <button
                    type="button"
                    class="border-gray-500 rounded border px-3 py-1.5 text-xs text-red-400"
                    :disabled="!matches || processing"
                    @click="emit('confirm')"
                >
                    Confirm deletion
                </button>
            </div>
        </div>
    </div>
</template>